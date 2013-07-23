<?php
/**
 * SiteHelper class
 *
 * @package    Helper
 * @subpackage SiteHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * Used to contain gather information about the site to be processed
 *
 * @package    Helper
 * @subpackage Site
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
class SiteHelper extends Helper {

    /**
     * Return a site as an array based on the alias
     *
     * @param $alias The site alias to locate.
     *
     * @return bool
     */
    public function getSiteAsArray($alias) {
        $staticsite = $this->getHelper('config')->get('sites', $alias);
        if (isset($staticsite)) {
            return $staticsite;
        } else {
            return false;
        }
    }

    /**
     * Return site as an object based on the alias
     *
     * @param $alias The site alias to locate.
     *
     * @return \stdClass
     */
    public function getSiteAsObject($alias) {
        $site = new \stdClass();

        $staticsite = $this->getHelperSet()->get('config')->get('sites', $alias);

        if (isset($staticsite)) {
            $site->name = $alias;
            foreach ($staticsite as $var => $value) {
                $site->{$var} = $value;
            }
        }
        return $site;
    }

    /**
     * Return the site's url based on teh alias
     *
     * @param $alias The site alias to locate.
     *
     * @return bool
     */
    public function getURL($alias) {
        $staticsite = $this->getHelperSet()->get('config')->get('sites', $alias);
        if (isset($staticsite)) {
            return $staticsite['url'];
        } else {
            return false;
        }
    }

    /**
     * Get an array of sites based on their type.
     *
     * @param string $type The type of sites that need to be returned
     *
     * @return array
     */
    public function getSites($type = 'all') {
        $sites       = array();
        $staticsites = $this->getHelperSet()->get('config')->getSection('sites');
        foreach ($staticsites as $name => $site) {
            if ($type == 'all' || in_array($type, $site['type'])) {
                $sites[$name] = $this->getSiteAsObject($name);
            }
        }
        return $sites;
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'site';
    }
}

?>
