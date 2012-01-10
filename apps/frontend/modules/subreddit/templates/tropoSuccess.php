<?php echo "<?php"; ?>

    $subreddit_id = <?php echo $subreddit_id ?>;
    $result = ask("Please identify your episode by the 32 digit ID hash found on the edit page.", array(
        "choices"=>"[32 DIGITS]",
        "timeout" => 15.0,
        "onTimeout" => "doThisOnTimeout",
        )
    );
    $id_hash = $result->value;

   say("Welcome to the hotline!");
   record("Tell us how you feel!", array (
          "beep" => true,
          "timeout" => 10,
          "recordFormat" => "audio/mp3",
          "recordURI"=>"<?php echo rtrim(sfConfig::get('app_web_app_api_location'), '/') . '/' ?>episode/upload?subreddit_id=" . $subreddit_id . "&id_hash=" . $id_hash,
          )
    );

    function doThisOnTimeout($event) {
        say("Call timed out.  Please try calling again.  Goodbye!");
        exit();
    }
?>