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
    protected $_episode_location = 'episode/future';
    //protected $_episode_location = 'episode';
    protected $_time_to_cache = 120;

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        header('content-type: text/xml');
        if (function_exists('apc_fetch'))
            $output = apc_fetch('index_feed', $success);
        else
            $success = false;
        if (!$success) {

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

            $output = $this->produceFeed($feedArray, 'atom');
            if (function_exists('apc_add'))
                apc_add('index_feed', $output, $this->_time_to_cache);
        }
        echo $output;
        throw new sfStopException();
    }

    /**
     * Executes user action
     *
     * @param sfRequest $request A request object
     */
    public function executeUser(sfWebRequest $request)
    {
        header('content-type: text/xml');
        $reddit_key = $request->getParameter('reddit_validation_key');
        $this->forward404Unless($reddit_key);
        if (function_exists('apc_fetch'))
            $output = apc_fetch('user_feed_' . $reddit_key, $success);
        else
            $success = false;
        if (!$success) {

            $user_data = Api::getInstance()->get('user?reddit_validation_key='
                    . $reddit_key, true);
            $users = ApiDoctrine::createObjectArray('sfGuardUser',
                                                    $user_data['body']);

            $user = (count($users) ? $users[0] : null);

            $this->forward404Unless($user);

            $episodes = $this->getUserEpisodes($user->getIncremented());

            $feedArray = $this->createFeedArray(
                    $episodes, $user->getUsername(),
                    $this->getController()->genUrl('@profile', true),
                                                   $this->getController()->genUrl('@feed_user_atom?reddit_validation_key=' . $user->getRedditValidationKey(),
                                                                                  true),
                                                                                  ProjectConfiguration::getApplicationName() . ' is a collection'
                    . ' of user-submitted audio episodes that cover an incredibly'
                    . ' wide variety of topics, organized around Reddit communities.'
                    . '  This is the customized feed for ' . $user->getUsername()
                    . ' and is composed of episodes from all their subreddit'
                    . ' communities.'
            );

            $output = $this->produceFeed($feedArray, 'atom');
            if (function_exists('apc_add'))
                apc_add('user_feed_' . $reddit_key, $output,
                        $this->_time_to_cache);
        }
        echo $output;
        throw new sfStopException();
    }

    /**
     * Executes subreddit action
     *
     * @param sfRequest $request A request object
     */
    public function executeSubreddit(sfWebRequest $request)
    {
        header('content-type: text/xml');
        $domain = $request->getParameter('domain');
        $this->forward404Unless($domain);
        if (function_exists('apc_fetch'))
            $output = apc_fetch('subreddit_feed' . $domain, $success);
        else
            $success = false;
        if (!$success) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . $domain, true);
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

            $output = $this->produceFeed($feedArray, 'atom');
            if (function_exists('apc_add'))
                apc_add('subreddit_feed' . $domain, $output,
                        $this->_time_to_cache);
        }
        echo $output;
        throw new sfStopException();
    }

    protected function getUserEpisodes($user_id)
    {
        $subreddit_ids = array();

        $membership_data = Api::getInstance()->get('membershiptype?'
                . 'sf_guard_user_id=' . $user_id
                . '&type=admin,moderator,user', true);
        $memberships = ApiDoctrine::createObjectArray('sfGuardUserSubredditMembership',
                                                      $membership_data['body']);
        foreach ($memberships as $membership) {
            if (!in_array($membership->getSubredditId(), $subreddit_ids))
                $subreddit_ids[] = $membership->getSubredditId();
        }

        $episodes = array();
        if (count($subreddit_ids)) {
            $episode_data = Api::getInstance()->get($this->_episode_location . '?subreddit_id='
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
        $episode_data = Api::getInstance()->get($this->_episode_location . '?subreddit_id='
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
            $episode_data = Api::getInstance()->get($this->_episode_location . '?subreddit_id='
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
                    'name' => array_key_exists($episode->getSfGuardUserId(), $this->users) ? $this->users[$episode->getSfGuardUserId()]->getUsername() : '',
                    'email' => array_key_exists($episode->getSfGuardUserId(), $this->users) ? $this->users[$episode->getSfGuardUserId()]->getEmailAddress() : '',
                    'uri' => $this->getController()->genUrl('@homepage', true),
                ),
                'audio_location' => ($episode->getApprovedAt() ? $episode->getRemoteUrl()
                            : $this->getController()->genUrl('@episode_audio?id='
                                . $episode->getId() . '&format='
                                . substr($episode->getAudioFile(), -3, 3), true)),
                'thumbnail' => '',
                'reddit_post_url' => ($episode->getRedditPostUrl() ? $episode->getRedditPostUrl()
                            : ''),
                'nsfw' => ($episode->getIsNsfw() ? 'nsfw' : ''),
            );
            if ($episode->getGraphicFile())
                $new_entry['thumbnail'] = str_replace('frontend_dev.php/', '',
                                                      $this->getController()->genUrl('@homepage',
                                                                                     true))
                        . trim(str_replace(sfConfig::get('sf_web_dir'), '',
                                                         ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()),
                                                         '/') . '/'
                        . $episode->getGraphicFile();
            $feedArray['entries'][] = $new_entry;
        }

        return $feedArray;
    }
    /* protected function produceRss($feedArray)
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

      $itunes_author = $doc->createElement('itunes:author', ProjectConfiguration::getApplicationName());
      $itunes_summary = $doc->createElement('itunes:summary', $feedArray['description']);
      $itunes_owner = $doc->createElement('itunes:owner');
      $itunes_name = $doc->createElement('itunes:name', ProjectConfiguration::getApplicationName());
      $itunes_email = $doc->createElement('itunes:email', ProjectConfiguration::getApplicationEmailAddress());
      $itunes_owner->appendChild($itunes_name);
      $itunes_owner->appendChild($itunes_email);

      $channel->appendChild($c_language);
      $channel->appendChild($c_title);
      $channel->appendChild($c_description);
      $channel->appendChild($c_pubdate);
      $channel->appendChild($c_generator);
      $channel->appendChild($c_link);
      $channel->appendChild($c_author);
      $channel->appendChild($c_dc_creator);
      $channel->appendChild($c_atom_link_one);
      $channel->appendChild($itunes_author);
      $channel->appendChild($itunes_summary);
      $channel->appendChild($itunes_name);
      $rss->appendChild($channel);

      foreach ($feedArray['entries'] as $entry) {
      $item = $doc->createElement('item');
      $i_title = $doc->createElement('title', $entry['title']);
      $thumbnail_tag = $entry['thumbnail'] ? '<p><img src="' . $entry['thumbnail'] . '"/></p>' : '';
      $i_description = $doc->createElement('description');
      $cdata_description = $doc->createCDATASection($thumbnail_tag . substr($entry['description'], 0, 500));
      $i_description->appendChild($cdata_description);
      $i_pubdate = $doc->createElement('pubDate', date('D, j M Y H:i:s O', $entry['released']));
      $i_link = $doc->createElement('link', $entry['link']);
      $i_guid = $doc->createElement('guid', $entry['link']);
      $i_author = $doc->createElement('author', $entry['author']['name']);
      $i_dc_creator = $doc->createElement('dc:creator', $entry['author']['name']);
      $i_content = $doc->createElement('content:encoded');
      if ($entry['thumbnail'])
      {
      $i_image = $doc->createElement('image');
      $i_url = $doc->createElement('url', $entry['thumbnail']);
      $i_link = $doc->createElement('link', $entry['link']);
      $i_image->appendChild($i_url);
      $i_image->appendChild($i_link);
      $item->appendChild($i_image);
      $i_media_content = $doc->createElement('media:content');
      $i_media_content->setAttribute('url', $entry['thumbnail']);
      $i_media_content->setAttribute('media', 'image');
      $i_mc_title = $doc->createElement('media:title', $entry['title']);
      $i_mc_title->setAttribute('type', 'html');
      $i_media_content->appendChild($i_mc_title);
      $item->appendChild($i_media_content);
      }
      if ($entry['reddit_post_url'])
      {
      $e_comments = $doc->createElement('comments', $entry['reddit_post_url']);
      $item->appendChild($e_comments);
      }
      $cdata_content = $doc->createCDATASection($thumbnail_tag . $entry['description']);
      $i_content->appendChild($cdata_content);
      $i_enclosure = $doc->createElement('enclosure');
      $i_enclosure->setAttribute('url', $entry['audio_location']);
      $audio_info = $this->getRemoteInfo($entry['audio_location']);
      $i_enclosure->setAttribute('type', $audio_info['type']);
      $i_enclosure->setAttribute('length', $audio_info['length']);

      $i_itunes_author = $doc->createElement('itunes:author', $entry['author']['name']);
      $i_itunes_summary = $doc->createElement('itunes:summary', strip_tags($entry['description']));

      $item->appendChild($i_title);
      $item->appendChild($i_description);
      $item->appendChild($i_pubdate);
      $item->appendChild($i_link);
      $item->appendChild($i_guid);
      $item->appendChild($i_author);
      $item->appendChild($i_dc_creator);
      $item->appendChild($i_content);
      $item->appendChild($i_enclosure);
      $item->appendChild($i_itunes_author);
      $item->appendChild($i_itunes_summary);
      $channel->appendChild($item);
      }

      $doc->formatOutput = true;
      $doc->preserveWhitespace = false;
      return $doc->saveXML();
      } */

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
        $generator = $doc->createElement('generator',
                                         ProjectConfiguration::getApplicationName() . ' Feed Module');
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
            $a_name = $doc->createElement('name',
                                          ProjectConfiguration::getApplicationName());
            $a_email = $doc->createElement('email',
                                           ProjectConfiguration::getApplicationEmailAddress());
            $a_uri = $doc->createElement('uri',
                                         $this->getController()->genUrl('@homepage',
                                                                        true));
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
            $thumbnail_tag = $entry['thumbnail'] ? '<p><img src="' . $entry['thumbnail'] . '"/></p>'
                        : '';
            $e_summary = $doc->createElement('summary');
            $e_summary->setAttribute('type', 'html');
            $cdata_summary = $doc->createCDATASection($thumbnail_tag . substr($entry['description'],
                                                                              0,
                                                                              500));
            $e_summary->appendChild($cdata_summary);
            $e_published = $doc->createElement('published',
                                               date('Y-m-d\TH:i:sP',
                                                    $entry['released']));
            $e_updated = $doc->createElement('updated',
                                             date('Y-m-d\TH:i:sP',
                                                  ($entry['modified'] > $entry['released']
                                        ? $entry['modified'] : $entry['released'])));
            $e_link = $doc->createElement('link');
            $e_link->setAttribute('rel', 'alternate');
            $e_link->setAttribute('type', 'text/html');
            $e_link->setAttribute('href', $entry['link']);
            $e_id = $doc->createElement('id', $entry['link']);
            $e_author = $doc->createElement('author'); {
                $ea_name = $doc->createElement('name', $entry['author']['name']);
                $e_author->appendChild($ea_name);
            }
            $e_enclosure = $doc->createElement('link');
            $e_enclosure->setAttribute('rel', 'enclosure');
            $audio_info = $this->getRemoteInfo($entry['audio_location']);
            $e_enclosure->setAttribute('type', $audio_info['type']);
            $e_enclosure->setAttribute('href', $entry['audio_location']);
            $e_enclosure->setAttribute('length', $audio_info['length']);
            $e_content = $doc->createElement('content');
            $e_content->setAttribute('xmlns:xhtml',
                                     'http://www.w3.org/1999/xhtml');
            $e_content->setAttribute('type', 'xhtml');
            {
                $fragment = $doc->createDocumentFragment();
                $fragment->appendXML($thumbnail_tag . $entry['content']);
                $e_xhtml_div = $doc->createElement('xhtml:div');
                $e_xhtml_div->setAttribute('xmlns:xhtml',
                                           'http://www.w3.org/1999/xhtml');
                $e_xhtml_div->appendChild($fragment);
                $e_content->appendChild($e_xhtml_div);
            }
            if ($entry['reddit_post_url']) {
                $e_comments = $doc->createElement('link');
                $e_comments->setAttribute('rel', 'replies');
                $e_comments->setAttribute('type', 'text/html');
                $e_comments->setAttribute('href', $entry['reddit_post_url']);
            }
            $fentry->appendChild($e_title);
            $fentry->appendChild($e_summary);
            $fentry->appendChild($e_published);
            $fentry->appendChild($e_updated);
            $fentry->appendChild($e_link);
            $fentry->appendChild($e_id);
            $fentry->appendChild($e_author);
            $fentry->appendChild($e_enclosure);
            $fentry->appendChild($e_content);
            $feed->appendChild($fentry);
        }

        $doc->formatOutput = true;
        $doc->preserveWhitespace = false;
        return $doc->saveXML();
    }

    protected function produceFeed($feedArray, $format = 'atom')
    {
        switch ($format)
        {
            case 'rss':
                return $this->produceRss($feedArray);
                break;
            case 'atom':
            default:
                return $this->produceAtom($feedArray);
                break;
        }
    }

    protected function getRemoteInfo($url)
    {
        $fp = fopen($url, 'r');
        $response = stream_get_meta_data($fp);
        $return = array(
            'length' => null,
            'type' => null,
        );
        foreach ($response['wrapper_data'] as $header) {
            if (strpos($header, 'Content-Length') !== false) {
                $return['length'] = (int) str_replace('Content-Length: ', '',
                                                      $header);
            }
            if (strpos($header, 'Content-Type') !== false) {
                $return['type'] = str_replace('Content-Type: ', '', $header);
            }
        }
        return $return;
    }
}
