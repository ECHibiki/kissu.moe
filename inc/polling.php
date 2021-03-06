<?php

	require_once 'inc/hazuki-coms.php';

	class Polling{
		//remove certain id from poll sql table
		static function removePoll($id){
		       	global $board;
            $query = prepare("DELETE FROM ``poll`` WHERE id=:id AND board=:board");
            $query->bindValue(':id', $id);
            $query->bindValue(':board', $board['uri']);
            $query->execute() or error(db_error($query));
		}
		//return nothing but add poll to sql table
		static function addPoll($poll_json, $id, &$pdo){
			global $board;
			$poll_obj = json_decode($poll_json, true);
        		$query = prepare("INSERT INTO ``poll`` VALUES (:id, :board, :questionaire, :multichoice, :post_count, :expires, :creation, :colors)");
        		$query->bindValue(':id', $id);
        		$query->bindValue(':board', $board['uri']);
       			$query->bindValue(':questionaire', json_encode($poll_obj['options']));
       			$query->bindValue(':multichoice', $poll_obj['multisel'] == 'on' ? 1 : 0);
			$query->bindValue(':post_count', $poll_obj['postthresh']);
        		$query->bindValue(':expires', intval($poll_obj['lifespan']) * 60 * 60 * 24 + time());
       			$query->bindValue(':creation', time());
       			$query->bindValue(':colors', json_encode($poll_obj['colors']));
	       		$query->execute() or error(db_error($query));
		}
		static function respondToPoll($id, $response_json, $board){

			$response_arr = json_decode($response_json, true);

			// check if response is empty
			if (empty($response_arr)){
				return "No options selected";
			}

			// check if response is not applicable(multichoice where not allowed, bad indexing etc.)
			$query = prepare("SELECT * FROM ``poll`` WHERE id=:id AND board=:board");
			$query->bindValue(':id', $id);
			$query->bindValue(':board', $board);
			$query->execute() or error(db_error($query));

      $poll_data = ($query->fetchAll(PDO::FETCH_ASSOC)[0]);
      $mul_choice = $poll_data['mutliple_choice'];
			if(count($response_arr) > 1 && $mul_choice == 0){
				return "Multichoice on radio";
			}

			// check if ip already voted
      $query = prepare("SELECT responses FROM ``responders`` WHERE poll_id=:id AND board=:board AND ip=:ip");
      $query->bindValue(':id', $id);
      $query->bindValue(':board', $board);
      $query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
      $query->execute() or error(db_error($query));
	  	$already_voted = false;
      $all_responses = $query->fetchAll(PDO::FETCH_ASSOC);
			if(!empty($all_responses)){
				$already_voted = true;
			}
			// check if  ip count does not reach threshold
			$post_count = 0;
			$post_threashold = $poll_data['post_count'];
			foreach(listBoards(true) as $uri){
                        	$query = prepare("SELECT COUNT(*) FROM ``posts_$uri`` WHERE ip=:ip");
				$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
				$query->execute() or error(db_error($query));
	                        $count = $query->fetchAll(PDO::FETCH_ASSOC)[0]["COUNT(*)"];
				$post_count += intval($count);
				if($post_count > $post_threashold)
					break;
			}
			if($post_count < $post_threashold){
				return "Not enough posts";
			}

			// check if poll is expired, but don't cause hard error
			$dead_time = $poll_data['expires'];
			if(time() < $dead_time || $poll_data['expires'] == $poll_data['created']){
				if($already_voted){
					// update id, sql will throw error if mismatched board and id
					$query = prepare("UPDATE ``responders`` SET responses=:response WHERE poll_id=:id AND board=:board AND ip=:ip");
								$query->bindValue(':id', $id);
								$query->bindValue(':board', $board);
								$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
								$query->bindValue(':response', $response_json);
								$query->execute() or error(db_error($query));
				}
				else{
					// input should be garunteed valid, sql will throw error if mismatched board and id
					$query = prepare("INSERT INTO ``responders`` VALUES (:id, :board, :ip, :response)");
								$query->bindValue(':id', $id);
								$query->bindValue(':board', $board);
								$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
								$query->bindValue(':response', $response_json);
								$query->execute() or error(db_error($query));
				}
				// id is OP in polling
				Hazuki::rebuildThread($id, $board);
	      Hazuki::decachePost($id, $board);
				Hazuki::rebuildCatalog($board);
				Hazuki::rebuildOverboard();
	      Hazuki::rebuildHome();
				Hazuki::send(true);
			}
		}

		static function getPollInfo($id, $board ){
			// get all responses to poll id
			$query = prepare("SELECT responses FROM ``responders`` WHERE poll_id=:id AND board=:board");
                        $query->bindValue(':id', $id);
                        $query->bindValue(':board', $board);
                        $query->execute() or error(db_error($query));
			$all_responses = $query->fetchAll(PDO::FETCH_ASSOC);

			// create array based on possible answers
			$query = prepare("SELECT * FROM ``poll`` WHERE id=:id AND board=:board");
			$query->bindValue(':id', $id);
			$query->bindValue(':board', $board);
			$query->execute() or error(db_error($query));
			$poll_data = ($query->fetchAll(PDO::FETCH_ASSOC));
			$questions = json_decode($poll_data[0]['questionaire_json']);
			$colors = json_decode($poll_data[0]['colors']);
			if(!$colors)
				$colors = [''];
			$question_arr = array();
			foreach($questions as $index=>$question)
				$question_arr[$index][$question] = 0;


			// tally up each response to answer
			foreach($all_responses as $single_response){
				$single_response = json_decode($single_response['responses']);
				foreach($single_response as $response_part){
					$question_arr[intval($response_part)][$questions[intval($response_part)]]++;
				}
			}
			array_push($question_arr, array("expires"=> $dead_time = $poll_data[0]['expires'], "created" => $poll_data[0]['created']));

			//return as json object
			return json_encode(['question'=>$question_arr, 'colors'=>$colors]);
		}

		//return json format of post form poll data
		static function formatFields($post){
			if(isset($post['pollopt1'])){
				$poll_obj = new stdClass();
				$poll_obj->multisel = $post['multisel'];
				$poll_obj->postthresh = $post['postthresh'];
				$poll_obj->lifespan = $post['lifespan'];
				$poll_obj->options = array();
				$poll_obj->colors = array();
				foreach($post as $key => $value){
						if(preg_match('/^pollopt\d+/', $key)){
							array_push($poll_obj->options, $value);
						}
						if(preg_match('/^color\d+/', $key)){
							if($value=="#000000"){
								$_POST[$key] =
								array_push($poll_obj->colors, sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
							} else{
								array_push($poll_obj->colors, $value);
							}
						}
				 }
				 while(count($poll_obj->options) > count($poll_obj->colors)){
					 array_push($poll_obj->colors , sprintf("#%06X", mt_rand(0,0xFFFFFF)));
				 }
				return json_encode($poll_obj);
			}
			else
				return null;
		}
		static function bodyAddablePoll($options, $multisel, $created_at, $expires){
			$input_selection = "";
			$type = "";
			if($multisel)
				$type = "checkbox";
			else
				$type = "radio";

			$lifespan = ($expires - $created_at) / (24 * 60 * 60);

			$options = json_decode($options, true);
			foreach($options as $index=>$option){
				$input_selection .= "<label><input type='$type' name='pollopt[]' value='$index'/>$option</label><br/>";
			}

			$form = "<div data-lifespan='$lifespan' data-creationtime='$created_at' class='pollform'>$input_selection<input type='submit' class='pollsubmit' onclick='return pollSubmit(this)' value='Cast Vote'><a href='javascript:void(0)' onclick='return viewPoll(this)'><br/>[View Responses]</a></div><br/>";

			return $form;

		}
	}

?>
