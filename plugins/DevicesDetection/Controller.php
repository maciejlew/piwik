<?php
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Controller;
use Piwik\ViewDataTable;
use Piwik\View;
use Piwik\Db;


/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DevicesDetection
 */
class Piwik_DevicesDetection_Controller extends Controller
{
    public function index($fetch = false)
    {
        $view = new View('@DevicesDetection/index');
        $view->deviceTypes = $view->deviceModels = $view->deviceBrands = $view->osReport = $view->browserReport = "blank";
        $view->deviceTypes = $this->getType(true);
        $view->deviceBrands = $this->getBrand(true);
        $view->deviceModels = $this->getModel(true);
        $view->osReport = $this->getOsFamilies(true);
        $view->browserReport = $this->getBrowserFamilies(true);
        echo $view->render();
    }

    public function getType($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrand($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getModel($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOsFamilies($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOsVersions($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserFamilies($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getBrowserVersions($fetch = false)
    {
        return ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * You may manually call this controller action to force re-processing of past user agents
     */
    public function refreshParsedUserAgents()
    {
        Piwik::checkUserIsSuperUser();
        $q = "SELECT idvisit, config_debug_ua FROM " . Common::prefixTable("log_visit");
        $res = Db::fetchAll($q);
        foreach ($res as $rec) {
            $UAParser = new UserAgentParserEnhanced($rec['config_debug_ua']);
            $UAParser->parse();
            echo "Processing idvisit = " . $rec['idvisit'] . "<br/>";
            echo "UserAgent string: " . $rec['config_debug_ua'] . "<br/> Decoded values:";
            $uaDetails = $this->getArray($UAParser);
            var_export($uaDetails);
            echo "<hr/>";
            $this->updateVisit($rec['idvisit'], $uaDetails);
            unset($UAParser);
        }
        echo "Please remember to truncate your archives !";
    }

    private function getArray(UserAgentParserEnhanced $UAParser)
    {
        $UADetails['config_browser_name'] = $UAParser->getBrowser("short_name");
        $UADetails['config_browser_version'] = $UAParser->getBrowser("version");
        $UADetails['config_os'] = $UAParser->getOs("short_name");
        $UADetails['config_os_version'] = $UAParser->getOs("version");
        $UADetails['config_device_type'] = $UAParser->getDevice();
        $UADetails['config_device_model'] = $UAParser->getModel();
        $UADetails['config_device_brand'] = $UAParser->getBrand();
        return $UADetails;
    }

    private function updateVisit($idVisit, $uaDetails)
    {
        $q = "UPDATE " . Common::prefixTable("log_visit") . " SET " .
            "config_browser_name = '" . $uaDetails['config_browser_name'] . "' ," .
            "config_browser_version = '" . $uaDetails['config_browser_version'] . "' ," .
            "config_os = '" . $uaDetails['config_os'] . "' ," .
            "config_os_version = '" . $uaDetails['config_os_version'] . "' ," .
            "config_device_type =  " . (isset($uaDetails['config_device_type']) ? "'" . $uaDetails['config_device_type'] . "'" : "NULL") . " ," .
            "config_device_model = " . (isset($uaDetails['config_device_model']) ? "'" . $uaDetails['config_device_model'] . "'" : "NULL") . " ," .
            "config_device_brand = " . (isset($uaDetails['config_device_brand']) ? "'" . $uaDetails['config_device_brand'] . "'" : "NULL") . "
                    WHERE idvisit = " . $idVisit;
        Db::query($q);
    }
}
