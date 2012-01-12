<?php echo "<?php"; ?>

say("Welcome to the phone recorder for the <?php echo $subreddit->getName() ?> subreddit in <?php echo ProjectConfiguration::getApplicationName() ?>!");
$valid_hash = false;
while (!$valid_hash)
{
    $result = ask("Please identify your episode by the <?php echo ProjectConfiguration::getTropoHashLength() ?> digit ID hash found on the edit page. You can either press the numbers or say them out loud.", array(
    "choices"=>"[<?php echo ProjectConfiguration::getTropoHashLength() ?> DIGITS]",
        "timeout" => 15.0,
        "interdigitTimeout" => 15.0,
        "onTimeout" => "doThisOnTimeout",
        )
    );
    $id_hash = $result->value;

    $output = json_decode(file_get_contents("<?php echo rtrim(sfConfig::get('app_web_app_api_location'), '/') . '/' ?>episodeassignment/validhash?subreddit_id=<?php echo $subreddit_id ?>&id_hash=" . $id_hash), true);
    $valid_hash = $output['is_valid'];
    if (!$valid_hash) {
        say("We're sorry; we couldn't find the ID hash of " . $id_hash);
    }
}

say("At the tone, please start recording your episode.");
record("When you are done, please hang up and we'll attach your recording to your episode assignment.", array (
    "beep" => true,
    "timeout" => 10,
    "silenceTimeout" => 20,
    "recordFormat" => "audio/mp3",
    "recordURI"=>"<?php echo rtrim(sfConfig::get('app_web_app_api_location'), '/') . '/' ?>episode/upload?subreddit_id=<?php echo $subreddit_id ?>&id_hash=" . $id_hash,
    "maxTime" => 28800,
    )
);

function doThisOnTimeout($event) {
    say("Call timed out.  Please try calling again.  Goodbye!");
    exit();
}

