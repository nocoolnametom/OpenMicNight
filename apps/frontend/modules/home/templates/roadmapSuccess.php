<h2>Roadmap</h2>

Here's some ideas we currently have for the future of the application:

<h4>Make it Prettier</h4>
Currently the app looks like it was designed by a programmer... because it was.  There's not much use of pretty AJAX effects, and much of the interaction with the site could benefit from some liberal use of JQuery and JQueryUI.  The episode sign-up, for instance, should probably be displayed more in a calendar-style that displays the next available slot and clicking on that slot should sign you up without even refreshing the page (there's that AJAX for you!).

<h4>Achievements</h4>
Other social sites that have been popular recently, like Fitocracy, employ a gaming-like aspect of achievements that can be gained through using the application.  This helps excite people to both use the app and to use it correctly.  An achievement system here could be based around such things as completing registration, submitting your first episode for approval, having your first episode released, and successfully reporting copyright infringement (that last one is obviously touchy and I'd want to do it right so that we have a balance between keeping the site nice and legal and not turning everyone into thought police for everyone else).  Please let us know what ideas or opinions you have on this topic.

<h4>Subreddit Audio File Editing</h4>
Currently we do not alter the uploaded audio files in any way, but it would be nice for Subreddits to add their own "bumper" intros and endings to personalize episodes released within their area.  And, frankly, it might be a good diea (though dangerously unpopular) to allow very short targeted audio adds to appear at the end of episodes to help fund the bandwidth costs of the app.  However, there's a lot wrapped up in that idea, including the fact that none of us are marketers and we don't have the first clue about how that process would even begin.  For now, we're going to look into the technical process of allowing Subreddits to define the opening and closing of their episodes.

<h4>Video</h4>
Video scares us, to be frank.  The bandwidth for audio is daunting enough, but to throw video into the mix makes it even worse.  Plus we wonder about the need for this.  YouTube offers the ability for anyone to upload videos that can then be linked to Reddit.  And we're really not sure how popular videocasts are.  But if there's enough call for this, we'll look into it.

<h4>Leave PHP Behind</h4>
I am a PHP programmer.  I enjoy the language, especially when combined with a strict and structured framework like Symfony.  But in the past few years I've worked on picking up the basics of many other languages such as Perl, Python, Ruby, and Java and I can recognize some of the shortcomings of PHP's easy and lazy style.  Some days I love dynamic typing and other days it's horrible.  I consider programming a case of the best tools both for the job <em>and</em> for the programmer, which for myself means PHP, but I understand that there are other solutions out there that may be preferred.  One of the ones that I find most intriguing is using Python, possibly with Django, to try and gain a speed improvement.  So in the long run we'll be looking into the possibility of at least moving the API system away from PHP to Python.  But this is a very long-term goal.

<div style="text-align: center; width: 100%; padding-top: 2em;">
    <div style="max-height: 30px; border: 2px solid white; box-shadow: 0px 0px 2px #000; font-weight: bolder; color: white; background-color: darkgrey ; width: 50%; margin: 0 auto;"><?php
include_partial('global/feedback_link', array(
    'feedback_text' => 'Let us know what ideas you have!',
    'link_style' => "color: white; text-decoration: none; text-shadow:1px 1px 3px #000000; padding: 7px 10px 5px 10px; display: block;"))
?>
    </div>
</div>
