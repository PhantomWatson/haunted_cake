<?php
namespace App\Map;

use Cake\Routing\Router;

class Map
{
    /**
     * @var array Array of Target objects
     */
    public $targets;

    /**
     * @var int 1 or 2
     */
    public $floor;

    /**
     * Constructor
     *
     * @param int $floor Floor
     * @return void
     */
    public function __construct($floor) {
        $this->targets = [];
        $this->floor = $floor;
    }

    /**
     * Adds a target object to this map's collection
     *
     * @param string $coords The coordinates in the imagemap
     * @param string|null $shortName The short name, matching a method name in RoomsController
     * @param string $longName A longer name, used in HTML 'title' attributes
     * @param null|string $move Optional, if a non-default move (action) is being conducted in a room
     * @return void
     */
    public function addTarget($coords, $shortName, $longName, $move = null)
    {
        $this->targets[] = new Target($coords, $shortName, $longName, $move);
    }

    /**
     * Returns the <area> tags used in the imagemap
     *
     * @return string
     */
    public function getAreaTags()
    {
        $retval = [];
        foreach ($this->targets as $target) {
            $coordCount = count(explode(',', $target->coords));
            $shape = ($coordCount == 4) ? 'rect' : 'poly';
            $area = '<area ';
            $area .= 'shape="' . $shape . '" ';
            $area .= 'coords="' . $target->coords . '" ';
            if ($target->shortName) {
                $url = [
                    'controller' => 'Rooms',
                    'action' => 'room',
                    'floor' => $this->floor,
                    'room' => $target->shortName
                ];
                if ($target->move) {
                    $url['move'] = $target->move;
                }
                $area .= 'href="' . Router::url($url) . '" ';
            } else {
                $area .= 'nohref ';
            }

            $area .= 'title="' . $target->longName . '" />';
            $retval[] = $area;
        }
        return implode("\n", $retval);
    }
}