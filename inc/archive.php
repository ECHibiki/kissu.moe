<?php

require_once('inc/functions.php');
require_once('inc/api.php');

class Archive {


  static public function restoreNewestItem(){
    global $config, $board;
    // If archiving is turned off return
    if(!$config['archive']['threads']){
      return;
    }

    // clean the buffer
    Hazuki::send();

    // get the thread
    Hazuki::restoreThreadFromArchive($board['uri']);
    $thread_id = Hazuki::send();


    return $thread_id;
  }

  // Archive thread and replies
  static public function archiveThread($thread_id) {
    global $config, $board;

    // If archiving is turned off return
    if(!$config['archive']['threads']){
      return;
    }

      Hazuki::insertIntoArchive($thread_id, $board['uri']);
      // the thread that's being sent will be deleted after this
      // will have to be inserted now
      Hazuki::send();
      // Purge Threads that have timed out
      self::purgeArchive();

      // Rebuild Archive Index
      self::buildArchiveIndex();
      return true;
  }



    // Removes Archived Threads that has outlived their lifetime
    static public function purgeArchive() {
      global $config, $board;

      // If archive is set to live forever return
      if(!$config['archive']['lifetime'])
          return;


      Hazuki::purgeArchive($board['uri']);
    }


    static public function RebuildArchiveIndexes() {
      global $config;

      // If archiving is turned off return
      if(!$config['archive']['threads'])
          return;

      // Purge Archive
      self::purgeArchive();

      // Rebuild Archive Index
      self::buildArchiveIndex();
    }


    static public function buildArchiveIndex() {
      global $config, $board;

      // If archiving is turned off return
      if(!$config['archive']['threads'])
          return;

      // Get archive List
      $archive = self::getArchiveList();

      if ($config['api']['enabled']){
      	$api = new Api();
      	$archive_json = json_encode($api->translateArchive($archive ));
      	$jsonFilename = $board['dir'] . $config['dir']['archive'] . 'archive.json';
      	file_write($jsonFilename, $archive_json);
      }


      foreach($archive as &$thread){
        $thread['archived_url'] = $config['dir']['res'] . sprintf($config['file_page'], $thread['id']);
      }

      $title = sprintf(_('Archived') . ' %s: ' . $config['board_abbreviation'], _('threads'), $board['uri']);
      $archive_page = Element('page.html', array(
          'config' => $config,
          'mod' => false,
          'hide_dashboard_link' => true,
          'boardlist' => createBoardList(false),
          'title' => $title,
          'subtitle' => "",
          'body' => Element("mod/archive_list.html", array(
              'config' => $config,
      		'thread_count' => count($archive),
              'board' => $board,
              'archive' => $archive
          ))
      ));

      file_write($config['dir']['home'] . $board['dir'] . $config['dir']['archive'] . $config['file_index'], $archive_page);
    }



    static public function getArchiveList($order_by_lifetime = false) {
      global $config, $board;

      $archive = false;
      $query = prepare(sprintf("SELECT `id`, `snippet` FROM ``archive_%s`` WHERE `lifetime` > :lifetime AND thread IS NULL", $board['uri']) . ($order_by_lifetime?" ORDER BY `lifetime` DESC":" ORDER BY `id` DESC"));
      $query->bindValue(':lifetime', strtotime("-" . $config['archive']['lifetime'] . " day"), PDO::PARAM_INT);
      $query->execute() or error(db_error());
      $archive = $query->fetchAll(PDO::FETCH_ASSOC);

      return $archive;
    }
}

?>
