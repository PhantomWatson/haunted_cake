<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Entity\Player;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    public function home()
    {
        $player = $this->getPlayer();
        $this->loadModel('Players');
        if ($this->request->is('post')) {
            $player = $this->Players->newEntity($this->request->data());
            if (false) {
                $this->Flash->error('Please correct the indicated errors');
            } else {
                $this->savePlayer($player);
                return $this->redirect([
                    'controller' => 'floors',
                    'action' => 'first'
                ]);
            }
        } elseif (! $player) {
            $player = $this->Players->newEntity([]);
        }

        $this->set([
            'player' => $player
        ]);
    }

    /**
     * Displays a view
     *
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display()
    {
        $path = func_get_args();

        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $e) {
            if (Configure::read('debug')) {
                throw $e;
            }
            throw new NotFoundException();
        }
    }

    /**
     * Method for lose condition
     *
     * @return void
     */
    public function lose()
    {

    }

    /**
     * Clears out stored data and sends player back to home page
     *
     * @return \Cake\Network\Response|null
     */
    public function restart()
    {
        $vars = $this->Cookie->read();
        foreach ($vars as $key => $value) {
            $this->Cookie->delete($key);
        }
        return $this->redirect([
            'action' => 'home'
        ]);
    }
}
