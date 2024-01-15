<?php
/**
 * User: joachimdorr
 * Date: 07.05.20
 * Time: 15:21
 */

namespace BSC\Controller;

use Psr\Http\Message\ServerRequestInterface;
use btu_portal;

class Timeline extends AbstractController
{
    public static function timelineAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $yUser = self::getYComAuthUser();
    
            $events = btu_portal::getParticipantEvents($yUser->getId(), 'DESC');
            $milestones = btu_portal::getParticipantMilestones($yUser->getId(), 'DESC');
            $consulting = btu_portal::getParticipantConsultingDates($yUser->getId(), 'DESC');
            
            $timeline = array();
            
            // events first
            if (is_array($events) && sizeof($events) > 0) {
                foreach ($events as &$event) {
                    $event['content_type'] = 'EVENT';
                    $event['url'] = '/event/'.$event['id'];
                    $ts = $event['startdate'];
        
                    while(isset($timeline[$ts])) {
                        $ts -= 1;
                    }
        
                    $timeline[$ts] = $event;
                }
            }
            
            // milestones second
            if (is_array($milestones) && sizeof($milestones) > 0) {
                // get milestones structure
                $milestoneTypesRaw = btu_portal::factory()->getArray("
                    SELECT
                        *
                    FROM
                        `rex_ycom_group`
                    WHERE
                        hidden = 0
                    ORDER BY
                        `id`
                ");
    
                $milestoneTypes = [];
                
                foreach($milestoneTypesRaw as $ms) {
                    $milestoneTypes[$ms['id']] = $ms;
                }
                
                foreach ($milestones as &$milestone) {
                    $milestone['content_type'] = 'MILESTONE';
                    $milestone['url'] = '/member/milestone/'.$milestone['id'];
                    $ts = (int)$milestone['timestamp'] * 1000;
                    $milestone['timestamp'] = $ts;
                    $milestone['scope'] = (isset($milestoneTypes[(int)$milestone['type']]) ? $milestoneTypes[(int)$milestone['type']] : null);
                    
                    while(isset($timeline[$ts])) {
                        $ts -= 1;
                    }
                    
                    $timeline[$ts] = array_filter(
                        $milestone,
                        function ($value, $key) {
                            return !in_array($key, ['updatedate', 'updateuser', 'createdate', 'createuser', 'cCreateDate', 'cEditDate']) && !is_null($value);
                        }, ARRAY_FILTER_USE_BOTH
                    );
                }
            }
            
            // consulting third
            if (is_array($consulting) && sizeof($consulting) > 0) {
                foreach ($consulting as &$date) {
                    $date['content_type'] = 'CONSULTING';
                    $date['url'] = '/consulting/'.$date['id'];
                    $ts = $date['timestamp'];
                    
                    while(isset($timeline[$ts])) {
                        $ts -= 1;
                    }
    
                    $timeline[$ts] = $date;
                }
            }

            krsort($timeline);
            $timeline = array_values($timeline);
            return self::response($timeline, 200, true);

        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}