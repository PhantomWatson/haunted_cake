<?php
namespace App\View\Helper;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\Helper;

class GameHelper extends Helper
{
    public $helpers = ['Html'];
    public $cookie;

    /**
     * Initialize function
     *
     * @param array $config Configuration
     */
    public function initialize(array $config)
    {
        $this->cookie = $this->_View->get('cookie');
        parent::initialize($config);
    }

    /**
     * Increments the amount of time spent
     *
     * @param int $periods Periods
     */
    public function spendTime($periods = 1)
    {
        $period1 = $this->cookie->read('time.period1');
        $period1 += $periods;
        $this->cookie->write('time.period1', $period1);
    }

    /**
     * Increments the amount of time available
     *
     * @param int $count Number of passes
     */
    public function addPasses($count = 1)
    {
        $period2 = $this->cookie->read('time.period2');
        $period2 += $count;
        $this->cookie->write('time.period2', $period2);
    }

    /**
     * Doubles the player's amount of passes
     *
     * @return void
     */
    public function doublePasses()
    {
        $period2 = $this->cookie->read('time.period2');
        $period2 *= 2;
        $this->cookie->write('time.period2', $period2);
    }

    /**
     * Decrements the amount of time available
     *
     * @param int $count Number of passes
     */
    public function removePasses($count = 1)
    {
        $period2 = $this->cookie->read('time.period2');
        $period2 -= $count;
        $this->cookie->write('time.period2', $period2);
    }

    /**
     * Returns a link to go back out into the hallway
     *
     * @param string $label Button label
     * @param int $spendTime How much "time spent" should be incremented
     * @return string
     */
    public function hallwayLink($label = null, $spendTime = 0)
    {
        $floor = $this->_View->get('floor');
        if (! $floor) {
            throw new InternalErrorException('Floor unknown');
        }
        if ($floor == 1) {
            $action = 'first';
        } elseif ($floor == 2) {
            $action = 'second';
        } else {
            throw new \InvalidArgumentException('Unrecognized floor: ' . $floor);
        }

        if (! $label) {
            $label = 'Go back out into the hallway';
        }
        $url = [
            'controller' => 'Floors',
            'action' => $action,
            'floor' => false,
            'room' => false
        ];
        if ($spendTime) {
            $url['?'] = ['ts' => $spendTime];
        }
        return $this->link(
            $label,
            $url,
            ['escape' => false]
        );
    }

    /**
     * Returns a game action link with special formatting
     *
     * @param string $label Label
     * @param array $url URL array
     * @return string
     */
    public function link($label, $url)
    {
        if (! isset($url['floor'])) {
            $url['floor'] = $this->_View->get('floor');
        }
        if (! isset($url['room'])) {
            $url['room'] = $this->_View->get('room');
        }

        // Don't show link if this room has been cleared
        if ($this->roomIsCleared($url['room'])) {
            return '';
        }

        return
            '<div class="btn-container">' .
            $this->Html->link(
                $label,
                $url,
                [
                    'class' => 'btn btn-lg btn-default',
                    'escape' => false
                ]
            ) .
            '</div>';
    }

    /**
     * Form opening tag
     *
     * @param string $method Post or get
     * @param array $url URL array for router
     * @return string
     */
    public function formStart($method, $url = [])
    {
        if (! isset($url['floor'])) {
            $url['floor'] = $this->_View->get('floor');
        }
        if (! isset($url['room'])) {
            $url['room'] = $this->_View->get('room');
        }

        return '<form method="' . $method . '" action="' . Router::url($url) . '" class="form-inline">';
    }

    /**
     * Form input
     *
     * @param
     * @return string
     */
    public function formInput($name, $placeholder = '')
    {
        return '<input type="text" name="' . $name . '" placeholder="' . $placeholder . '" class="form-control" />';
    }

    /**
     * Submit button
     *
     * @param string $label Button label
     * @return string
     */
    public function formSubmit($label)
    {
        return '<input type="submit" value="' . $label . '" class="btn btn-lg btn-default" />';
    }

    /**
     * Form close tag
     *
     * @return string
     */
    public function formEnd()
    {
        return '</form>';
    }

    /**
     * Records a quest as having been completed
     *
     * @param string $quest Quest identifier
     */
    public function completeQuest($quest)
    {
        $quests = $this->cookie->read('quests');
        $quests .= $quest;
        $this->cookie->write('quests', $quests);
    }

    /**
     * Returns the player's current title
     *
     * @param null|string $questJustCompleted Identifier for quest that has just been completed
     * @return string
     */
    public function getTitle($questJustCompleted = null)
    {
        $quests = $this->cookie->read('quests');
        $playersTable = TableRegistry::get('Players');
        $sex = $this->cookie->read('player.sex');
        $title = $playersTable->getTitle($quests, $sex);
        if ($questJustCompleted) {
            $titleComponent = $playersTable->getTitleComponent($questJustCompleted, $sex);
            $title = str_replace($titleComponent, '<strong>' . $titleComponent . '</strong>', $title);
        }
        return $title;
    }

    /**
     * Returns whether or not the user has completed the indicated quest
     *
     * @param string $quest Quest identifier
     * @return bool
     */
    public function questCompleted($quest)
    {
        $quests = $this->cookie->read('quests');
        return strpos($quests, $quest) !== false;
    }

    /**
     * Changes the player's GPA
     *
     * @param int $value GPA
     */
    public function changeGpa($value)
    {
        $this->cookie->write('player.gpa', $value);
    }

    /**
     * Remembers giving Ms. Bly an answer
     *
     * @param string $answer Answer value
     * @return void
     */
    public function setBlyAnswer($answer)
    {
        $this->cookie->write("game.blyanswers.$answer", true);
    }

    /**
     * Returns whether or not
     *
     * @param string $answer Answer value
     * @return bool
     */
    public function blyAnswerGiven($answer)
    {
        return (bool)$this->cookie->read("game.blyanswers.$answer");
    }

    /**
     * Changes all of the vowels in $name
     *
     * @param string $name Name
     * @return mixed
     */
    public function scrambleVowels($name)
    {

        $name = str_split($name);
        foreach ($name as &$letter) {
            $letter = $this->scrambleVowel($letter);
        }
        return implode('', $name);
    }

    /**
     * Scrambles a single vowel (or returns the consonant given to it)
     *
     * @param string $letter Letter
     * @return string
     */
    public function scrambleVowel($letter)
    {
        $vowels = ['a', 'e', 'i', 'o', 'u'];
        if (! in_array(strtolower($letter), $vowels)) {
            return $letter;
        }
        $alternates = array_filter($vowels, function ($var) use ($letter) {
            return $var != $letter;
        });

        // Lowercase
        if ($letter == strtolower($letter)) {
            return $alternates[array_rand($alternates)];
        }

        // Uppercase
        return strtoupper($alternates[array_rand($alternates)]);
    }

    /**
     * Sets the player's name
     *
     * @param string $name Name
     * @return void
     */
    public function changeName($name)
    {
        $this->cookie->write('player.name', $name);
    }

    /**
     * Marks a room as having been "cleared", making it henceforth inaccessible
     *
     * @return void
     */
    public function clearRoom()
    {
        $room = $this->_View->get('room');
        if (! $room) {
            throw new InternalErrorException('Error clearing room. Room unknown.');
        }
        $this->cookie->write("cleared-rooms.$room", true);
    }

    /**
     * Returns an array of cleared room identifiers
     *
     * @return array
     */
    public function getClearedRooms()
    {
        $clearedRooms = $this->cookie->read('cleared-rooms');
        return is_array($clearedRooms) ? array_keys($clearedRooms) : [];
    }

    /**
     * Returns true if a room has been cleared
     *
     * @param string $room Room identifier
     * @return bool
     */
    public function roomIsCleared($room)
    {
        return (bool)$this->cookie->read("cleared-rooms.$room");
    }
}
