<?php
/**
 * User: joachimdorr
 * Date: 22.04.20
 * Time: 12:32
 */

namespace BSC\Trait;


use btu_portal;
use BSC\Model\Milestone;
use BSC\Model\MilestoneCreate;
use BSC\Model\MilestonePatch;

trait ModelMapper
{
    /**
     * @param array $data
     * @return Milestone|null
     * @author Joachim Doerr
     */
    protected static function mapDataToMilestone(array $data)
    {
        $milestone = null;
        if (is_array($data) && sizeof($data) > 0) {
            if (isset($data['type'])) {
                $milestone = new Milestone();
                $type = (array_key_exists($data['type'], btu_portal::$fieldDependencies)) ? btu_portal::$fieldDependencies[$data['type']] : '';

                $milestone->setType($type);

                $map = [];
                switch ($type) {
                    case "school":
                        $map = [
                            $type . '_type' => 'setTypeOfType',
                            $type . '_grade' => 'setGrade',
                            $type . '_graduation' => 'setGraduation',
                        ];
                        break;
                    case "university":
                        $map = [
                            $type . '_type' => 'setTypeOfType',
                            $type . '_semester' => 'setGrade',
                            $type . '_graduation' => 'setGraduation',
                            $type . '_course' => 'setCourse',
                            $type . '_student_number' => 'setStudentNumber',
                            $type . '_educational_achievement' => 'setEducationalAchievement',
                        ];
                        break;
                    case "voluntary":
                        $map = [
                            $type . '_type' => 'setTypeOfType',
                            $type . '_educational_achievement' => 'setEducationalAchievement',
                        ];
                        break;
                    case "apprentice":
                        $map = [
                            $type . '_type' => 'setTypeOfType',
                            $type . '_year' => 'setGrade',
                            $type . '_graduation' => 'setGraduation',
                            $type . '_educational_achievement' => 'setEducationalAchievement',
                        ];
                        break;
                    case "else":
                        $map = [
                            $type . '_educational_achievement' => 'setEducationalAchievement',
                        ];
                        break;
                }
                $map = array_filter(array_merge($map, [
                    'id' => 'setId',
                    'status_from' => 'setStatusFrom',
                    'status_until' => 'setStatusUntil',
                    $type . '_name' => 'setName',
                    $type . '_location' => 'setLocation',
                    $type . '_after' => 'setAfter',
                ]));
                foreach ($map as $key => $setter) {
                    $milestone->$setter((isset($data[$key])) ? $data[$key] : null);
                }
            }
        }
        return $milestone;
    }

    /**
     * @param MilestoneCreate|MilestonePatch|Milestone $object
     * @return array
     * @author Joachim Doerr
     */
    protected static function mapMilestoneToData($object)
    {
        $type = 0;
        foreach (btu_portal::$fieldDependencies as $key => $value)
            if ($object->getType() == $value)
                $type = $key;

        $data = [
            $object->getType() . '_name' => $object->getName(),
            $object->getType() . '_location' => $object->getLocation(),
            $object->getType() . '_after' => $object->getAfter(),
        ];

        switch ($object->getType()) {
            case "school":
                $data = array_merge($data, [
                    $object->getType() . '_type' => $object->getTypeOfType(),
                    $object->getType() . '_grade' => $object->getGrade(),
                    $object->getType() . '_graduation' => $object->getGraduation(),
                ]);
                // TODO validate school stuff
                break;
            case "university":
                $data = array_merge($data, [
                    $object->getType() . '_type' => $object->getTypeOfType(),
                    $object->getType() . '_semester' => $object->getGrade(),
                    $object->getType() . '_graduation' => $object->getGraduation(),
                    $object->getType() . '_course' => $object->getCourse(),
                    $object->getType() . '_student_number' => $object->getStudentNumber(),
                    $object->getType() . '_educational_achievement' => $object->getEducationalAchievement(),
                ]);
                // TODO validate university stuff
                break;
            case "voluntary":
                $data = array_merge($data, [
                    $object->getType() . '_type' => $object->getTypeOfType(),
                    $object->getType() . '_educational_achievement' => $object->getEducationalAchievement(),
                ]);
                // TODO validate voluntary stuff
                break;
            case "apprentice":
                $data = array_merge($data, [
                    $object->getType() . '_type' => $object->getTypeOfType(),
                    $object->getType() . '_year' => $object->getGrade(),
                    $object->getType() . '_graduation' => $object->getGraduation(),
                    $object->getType() . '_educational_achievement' => $object->getEducationalAchievement(),
                ]);
                // TODO validate apprentice stuff
                break;
            case "else":
                $data = array_merge($data, [
                    $object->getType() . '_educational_achievement' => $object->getEducationalAchievement(),
                ]);
                // TODO validate else stuff
                break;
        }

        if ($object instanceof MilestoneCreate) {
            $data['participant'] = $object->getMemberId();
        }

        return (array_merge([
            'type' => $type,
            'status_from' => $object->getStatusFrom(),
            'status_until' => $object->getStatusUntil(),
        ], $data));
    }

}