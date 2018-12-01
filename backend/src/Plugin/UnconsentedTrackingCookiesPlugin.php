<?php

namespace Ontic\Iuris\Plugin;

use Ontic\Iuris\Interfaces\IPlugin;
use Ontic\Iuris\Model\AnalysisDetail;
use Ontic\Iuris\Model\AnalysisRequest;
use Ontic\Iuris\Model\Flag;

class UnconsentedTrackingCookiesPlugin implements IPlugin
{
    /**
     * @return string
     */
    function getName()
    {
        return 'unconsented_tracking_cokies';
    }

    /**
     * @param AnalysisRequest $request
     * @param array $config
     * @return AnalysisDetail
     */
    function analyze(AnalysisRequest $request, array $config)
    {
        $trackingCookies = [];
        $sessionCookies = [];
        $unknownCookies = [];
        
        $cookies = $request->getWebdriver()->manage()->getCookies();
        $totalScore = 0;
        
        foreach($cookies as $cookie)
        {
            $name = $cookie->getName();
            
            if(isset($config['tracking_cookies'][$name]))
            {
                $trackingCookies[] = '✗ ' . $config['tracking_cookies'][$name];
            }
            elseif(isset($config['session_cookies'][$name]))
            {
                $sessionCookies[] = '✓ ' . $config['session_cookies'][$name];
                $totalScore += 100;
            }
            else
            {
                $unknownCookies[] = '⚠ ' . $name;
                $totalScore += 50;
            }
        }
        
        $message = array_merge($trackingCookies, $sessionCookies, $unknownCookies);
        $message = implode("\n", $message);
        
        return new AnalysisDetail(
            $this->getName(),
            Flag::Scorable,
            $totalScore / count($cookies),
            $message);
    }
}