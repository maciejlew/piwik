<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * Reusable fixture. Adds 20,000 sites and tracks one pageview for each on one day.
 */
class Piwik_Test_Fixture_ManyThousandSitesOneVisitEach
{
    public $date = '2010-01-01';
    public $period = 'day';
    public $siteCount = 20000;
    public $idSite = 'all';
    
    public function setUp()
    {
        for ($i = 0; $i != $this->siteCount; ++$i) {
            $idSite = Test_Piwik_BaseFixture::createWebsite(
                $this->date, $ecommerce = 1, $siteName = "Site #$i", $siteUrl = "http://site$i.com/");
            
            Piwik_Goals_API::getInstance()->addGoal($idSite, 'all', 'url', 'http', 'contains', false, 5);
        }
        
        // track one visit for each site
        $t = BenchmarkTestCase::getLocalTracker(1);
        $t->setForceVisitDateTime(Date::factory($this->date)->addHour(6));
        for ($idSite = 1; $idSite < $this->siteCount + 1; ++$idSite) {
            $ip = "157.5.6.4";
            $t->setIp($ip);
            $t->setNewVisitorId();
            
            $t->setIdSite($idSite);
            
            $t->setUrl("http://site" . ($idSite - 1) . ".com/page.html");
            $t->doTrackPageView('page title');
        }
    }
}
