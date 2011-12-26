<?php

/**
 * feed actions.
 *
 * @package    OpenMicNight
 * @subpackage feed
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class feedActions extends sfActions
{
    protected $_users = array();

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $episodes = $this->getIndexEpisodes();

        $feedArray = $this->createFeedArray(
                $episodes, 'Main Feed',
                $this->generateUrl('homepage', array(), true),
                                   $this->getController()->genUrl('@feed_index_atom',
                                                                  true),
                                                                  ProjectConfiguration::getApplicationName() . ' is a collection'
                . ' of user-submitted audio episodes that cover an incredibly'
                . ' wide variety of topics, organized around Reddit communities.'
                . '  You can customize your own personal feed by registering a'
                . ' user at the home page.'
        );

        $this->produceFeed($feedArray, $request->getParameter('format', 'atom'));
    }

    /**
     * Executes user action
     *
     * @param sfRequest $request A request object
     */
    public function executeUser(sfWebRequest $request)
    {
        $this->foward404Unless($request->getParameter('reddit_key'));

        $user_data = Api::getInstance()->get('user?reddit_validation_key='
                . $request->getParameter('reddit_key'), true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);

        $user = (count($users) ? $users[0] : null);

        $this->forward404Unless($user);

        $episodes = $this->getUserEpisodes($user->getIncremented());

        $feedArray = $this->createFeedArray(
                $episodes, $user->getUsername(),
                $this->getController()->genUrl('@feed_user_rss?reddit_valdiation_key=' . $user->getRedditValidationKey(),
                                               true),
                                               $this->getController()->genUrl('@feed_user_atom?reddit_valdiation_key=' . $user->getRedditValidationKey(),
                                                                              true),
                                                                              ProjectConfiguration::getApplicationName() . ' is a collection'
                . ' of user-submitted audio episodes that cover an incredibly'
                . ' wide variety of topics, organized around Reddit communities.'
                . '  This is the customized feed for ' . $user->getUsername()
                . ' and is composed of episodes from all your subreddit'
                . ' communities.'
        );

        $this->produceFeed($feedArray, $request->getParameter('format', 'atom'));
    }

    /**
     * Executes subreddit action
     *
     * @param sfRequest $request A request object
     */
    public function executeSubreddit(sfWebRequest $request)
    {
        $this->forward404Unless($request->getParameter('domain'));

        $subreddit_data = Api::getInstance()->get('subreddit?domain='
                . $request->getParameter('domain'), true);
        $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);

        $subreddit = (count($subreddits) ? $subreddits[0] : null);

        $this->forward404Unless($subreddit);

        $episodes = $this->getSubredditEpisodes($subreddit->getIncremented());

        $feedArray = $this->createFeedArray(
                $episodes, $subreddit->getName(),
                $this->getController()->genUrl('@subreddit_index?domain='
                        . $subreddit->getDomain(), true),
                                               $this->getController()->genUrl('@feed_subreddit_atom?domain='
                        . $subreddit->getDomain(), true),
                                                                              ProjectConfiguration::getApplicationName() . ' is a collection'
                . ' of user-submitted audio episodes that cover an incredibly'
                . ' wide variety of topics, organized around Reddit communities.'
                . '  This is the customized feed for the '
                . $subreddit->getName() . ' subreddit community.'
        );

        $this->produceFeed($feedArray, $request->getParameter('format', 'atom'));
    }

    protected function getUserEpisodes($user_id)
    {
        $subreddit_ids = array();

        $membership_data = Api::getInstance()->get('membershiptype?'
                . 'sf_guard_user_id=' . $user_id
                . '&type=admin,moderator,user', true);
        $memberships = ApiDoctrine::createQuickObjectArray($membership_data['body']);
        foreach ($memberships as $membership) {
            if (!in_array($membership->getSubredditId(), $subreddit_ids))
                $subreddit_ids[] = $membership->getSubredditId();
        }

        $episodes = array();
        if (count($subreddit_ids)) {
            $episode_data = Api::getInstance()->get('episode?subreddit_id='
                    . implode(',', $subreddit_ids), true);
            $episodes = ApiDoctrine::createQuickObjectArray($episode_data['body']);
        }

        $user_ids = array();
        foreach ($episodes as $episode) {
            if (!in_array($episode->getSfGuardUserId(), $user_ids))
                $user_ids[] = $episode->getSfGuardUserId();
        }
        $user_data = Api::getInstance()->get('user?id='
                . implode(',', $user_ids), true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);

        $this->users = array();
        foreach ($users as $user) {
            $this->users[$user->getIncremented()] = $user;
        }

        return $episodes;
    }

    protected function getSubredditEpisodes($subreddit_id)
    {
        $episode_data = Api::getInstance()->get('episode?subreddit_id='
                . $subreddit_id, true);
        $episodes = ApiDoctrine::createQuickObjectArray($episode_data['body']);

        $user_ids = array();
        foreach ($episodes as $episode) {
            if (!in_array($episode->getSfGuardUserId(), $user_ids))
                $user_ids[] = $episode->getSfGuardUserId();
        }
        $user_data = Api::getInstance()->get('user?id='
                . implode(',', $user_ids), true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);

        $this->users = array();
        foreach ($users as $user) {
            $this->users[$user->getIncremented()] = $user;
        }

        return $episodes;
    }

    protected function getIndexEpisodes()
    {
        $subreddit_ids = array();

        $subreddits = sfConfig::get('app_web_app_feed_default_subreddits');
        if (count($subreddits)) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . implode(',', $subreddits), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            foreach ($subreddits as $subreddit) {
                if (!in_array($subreddit->getIncremented(), $subreddit_ids))
                    $subreddit_ids[] = $subreddit->getIncremented();
            }
        }

        $episodes = array();
        if (count($subreddit_ids)) {
            $episode_data = Api::getInstance()->get('episode?subreddit_id='
                    . implode(',', $subreddit_ids), true);
            $episodes = ApiDoctrine::createQuickObjectArray($episode_data['body']);
        }

        $user_ids = array();
        foreach ($episodes as $episode) {
            if (!in_array($episode->getSfGuardUserId(), $user_ids))
                $user_ids[] = $episode->getSfGuardUserId();
        }
        $user_data = Api::getInstance()->get('user?id='
                . implode(',', $user_ids), true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);

        $this->users = array();
        foreach ($users as $user) {
            $this->users[$user->getIncremented()] = $user;
        }

        return $episodes;
    }

    protected function createFeedArray($episode_array, $title, $link,
                                       $atom_link, $description)
    {
        $feedArray = array(
            'title' => ProjectConfiguration::getApplicationName() . ' - ' . $title,
            'link' => $link,
            'atom_link' => $atom_link,
            'description' => $description,
            'language' => 'en-us',
            'charset' => sfConfig::get('sf_charset'),
            'pubDate' => time(),
            'entries' => array()
        );

        /* get the data from the db or outher source */
        foreach ($episode_array as $episode) {
            $new_entry = array(
                'title' => $episode->getTitle(),
                'link' => $this->getController()->genUrl('@episode_show?id='
                        . $episode->getIncremented()
                        , true),
                'guid' => $episode->getIncremented(),
                'description' => html_entity_decode($this->getUser()->formatMarkdown($episode->getDescription())),
                'content' => html_entity_decode($this->getUser()->formatMarkdown($episode->getDescription())),
                'lastUpdate' => strtotime($episode->getReleaseDate()),
                'modified' => strtotime($episode->getUpdatedAt()),
                'released' => strtotime($episode->getReleaseDate()),
                'author' => array(
                    'name' => $this->users[$episode->getSfGuardUserId()]->getUsername(),
                    'email' => $this->users[$episode->getSfGuardUserId()]->getEmailAddress(),
                    'uri' => $this->getController()->genUrl('@homepage', true),
                ),
                'audio_location' => ($episode->getApprovedAt() ? $episode->getRemoteUrl()
                            : $this->getController()->genUrl('@episode_audio?id='
                                . $episode->getId() . '&format='
                                . substr($episode->getAudioFile(), -3, 3), true)),
            );
            if ($episode->getGraphicFile())
                $new_entry['thumbnail'] = $this->getController()->genUrl('@homepage',
                                                                         true)
                        . ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()
                        . $episode->getGraphicFile();
            $feedArray['entries'][] = $new_entry;
        }

        return $feedArray;
    }

    protected function produceRss($feedArray)
    {
        // RSS 2.0

        $doc = new DomDocument('1.0', 'utf-8');

        $rss = $doc->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $rss->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $doc->appendChild($rss);

        $channel = $doc->createElement('channel');
        $doc->appendChild($channel);

        $c_language = $doc->createElement('language', $feedArray['language']);
        $c_title = $doc->createElement('title', $feedArray['title']);
        $c_description = $doc->createElement('description', $feedArray['description']);
        $c_pubdate = $doc->createElement('pubDate', date('D, j M Y H:i:s O'));
        $c_generator = $doc->createElement('generator', ProjectConfiguration::getApplicationName() . ' Feed Module');
        $c_link = $doc->createElement('link', $feedArray['link']);
        $c_author = $doc->createElement('author', ProjectConfiguration::getApplicationEmailAddress() . ' (' . ProjectConfiguration::getApplicationName() . ')');
        $c_dc_creator = $doc->createElement('dc:creator', ProjectConfiguration::getApplicationName());
        $c_atom_link_one = $doc->createElement('atom:link');
        $c_atom_link_one->setAttribute('rel', 'self');
        $c_atom_link_one->setAttribute('type', 'application/atom+xml');
        $c_atom_link_one->setAttribute('href', $feedArray['atom_link']);
        $c_atom_link_two = $doc->createElement('atom:link');
        $c_atom_link_two->setAttribute('rel', 'hub');
        $c_atom_link_two->setAttribute('href', 'http://pubsubhubbub.appspot.com/');

        $channel->appendChild($c_language);
        $channel->appendChild($c_title);
        $channel->appendChild($c_description);
        $channel->appendChild($c_pubdate);
        $channel->appendChild($c_generator);
        $channel->appendChild($c_link);
        $channel->appendChild($c_author);
        $channel->appendChild($c_dc_creator);
        $channel->appendChild($c_atom_link_one);
        $channel->appendChild($c_atom_link_two);
        $rss->appendChild($channel);

        foreach ($feedArray['entries'] as $entry) {
            $item = $doc->createElement('item');
            $i_title = $doc->createElement('title', $entry['title']);
            $i_description = $doc->createElement('description');
            $cdata_description = $doc->createCDATASection($entry['description']);
            $i_description->appendChild($cdata_description);
            $i_pubdate = $doc->createElement('pubDate', date('D, j M Y H:i:s O', $entry['released']));
            $i_link = $doc->createElement('link', $entry['link']);
            $i_guid = $doc->createElement('guid', $entry['link']);
            $i_author = $doc->createElement('author', $entry['author']['name']);
            $i_dc_creator = $doc->createElement('dc:creator', $entry['author']['name']);
            $i_content = $doc->createElement('content:encoded');
            $cdata_content = $doc->createCDATASection($entry['description']);
            $i_content->appendChild($cdata_content);

            $item->appendChild($i_title);
            $item->appendChild($i_description);
            $item->appendChild($i_pubdate);
            $item->appendChild($i_link);
            $item->appendChild($i_guid);
            $item->appendChild($i_author);
            $item->appendChild($i_dc_creator);
            $item->appendChild($i_content);
            $channel->appendChild($item);
        }
        
        header('content-type: text/xml');
        $doc->formatOutput = true;
        $doc->preserveWhitespace = false;
        echo $doc->saveXML();
    }

    protected function produceAtom($feedArray)
    {
        // Atom 1.0

        $doc = new DomDocument('1.0', 'utf-8');

        $feed = $doc->createElement('feed');
        $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $feed->setAttribute('xml:lang', $feedArray['language']);
        $doc->appendChild($feed);

        $title = $doc->createElement('title', $feedArray['title']);
        $title->setAttribute('type', 'text');
        $subtitle = $doc->createElement('subtitle', $feedArray['description']);
        $subtitle->setAttribute('type', 'text');
        $updated = $doc->createElement('updated', date('Y-m-d\TH:i:sP'));
        $generator = $doc->createElement('generator', ProjectConfiguration::getApplicationName() . ' Feed Module');
        $link_alternate = $doc->createElement('link');
        $link_alternate->setAttribute('rel', 'alternate');
        $link_alternate->setAttribute('type', 'text/html');
        $link_alternate->setAttribute('href', $feedArray['link']);
        $link_self = $doc->createElement('link');
        $link_self->setAttribute('rel', 'self');
        $link_self->setAttribute('type', 'application/atom+xml');
        $link_self->setAttribute('href', $feedArray['atom_link']);
        $id = $doc->createElement('id', $feedArray['link']);
        $author = $doc->createElement('author'); {
            $a_name = $doc->createElement('name', ProjectConfiguration::getApplicationName());
            $a_email = $doc->createElement('email', ProjectConfiguration::getApplicationEmailAddress());
            $a_uri = $doc->createElement('uri', $this->getController()->genUrl('@homepage', true));
            $author->appendChild($a_name);
            $author->appendChild($a_email);
            $author->appendChild($a_uri);
        }
        $feed->appendChild($title);
        $feed->appendChild($subtitle);
        $feed->appendChild($updated);
        $feed->appendChild($generator);
        $feed->appendChild($link_alternate);
        $feed->appendChild($link_self);
        $feed->appendChild($id);
        $feed->appendChild($author);

        foreach ($feedArray['entries'] as $entry) {
            $fentry = $doc->createElement('entry');
            $fentry->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
            $e_title = $doc->createElement('title');
            $e_title->setAttribute('type', 'html');
            $cdata_title = $doc->createCDATASection($entry['title']);
            $e_title->appendChild($cdata_title);
            $e_summary = $doc->createElement('summary');
            $e_summary->setAttribute('type', 'html');
            $cdata_summary = $doc->createCDATASection($entry['description']);
            $e_summary->appendChild($cdata_summary);
            $e_published = $doc->createElement('published', date('Y-m-d\TH:i:sP', $entry['released']));
            $e_updated = $doc->createElement('updated', date('Y-m-d\TH:i:sP', ($entry['modified'] > $entry['released'] ? $entry['modified'] : $entry['released'])));
            $e_link = $doc->createElement('link');
            $e_link->setAttribute('rel', 'alternate');
            $e_link->setAttribute('type', 'text/html');
            $e_link->setAttribute('href', $entry['link']);
            $e_id = $doc->createElement('id', $entry['link']);
            $e_author = $doc->createElement('author'); {
                $ea_name = $doc->createElement('name', $entry['author']['name']);
                $e_author->appendChild($ea_name);
            }
            $e_content = $doc->createElement('content');
            $e_content->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
            $e_content->setAttribute('type', 'xhtml');
            {
                $fragment = $doc->createDocumentFragment();
                $fragment->appendXML($entry['content']);
                $e_xhtml_div = $doc->createElement('xhtml:div');
                $e_xhtml_div->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
                $e_xhtml_div->appendChild($fragment);
                $e_content->appendChild($e_xhtml_div);
            }
            $fentry->appendChild($e_title);
            $fentry->appendChild($e_summary);
            $fentry->appendChild($e_published);
            $fentry->appendChild($e_updated);
            $fentry->appendChild($e_link);
            $fentry->appendChild($e_id);
            $fentry->appendChild($e_author);
            $fentry->appendChild($e_content);
            $feed->appendChild($fentry);
        }
        
        
        header('content-type: text/xml');
        $doc->formatOutput = true;
        $doc->preserveWhitespace = false;
        echo $doc->saveXML();
    }

    protected function produceFeed($feedArray, $format = 'atom')
    {
        switch($format)
        {
        case 'rss':
            $this->produceRss($feedArray);
            break;
        case 'atom':
        default:
            $this->produceAtom($feedArray);
            break;
        }
        
        throw new sfStopException();
    }
}
