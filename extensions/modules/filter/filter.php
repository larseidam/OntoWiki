<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – filter
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_filter
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: filter.php 4279 2009-10-11 23:20:25Z jonas.brekle@gmail.com $
 */
class FilterModule extends OntoWiki_Module
{
	
    public function init()
    {
        $this->store = $this->_owApp->erfurt->getStore();
        $this->model = $this->_owApp->selectedModel;
        $session  = $this->_owApp->session;
        $this->titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);

        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'resources/filter.css');
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/jquery.dump.js');
        
        $this->view->properties = $this->_owApp->instances->getAllProperties();
        $this->view->inverseProperties = $this->_owApp->instances->getAllReverseProperties();

        $this->view->filter = $session->filter;        
    }
	
	
    public function getTitle()
    {
        return 'Filter';
    }
    
    public function getContents()
    {
        if(is_array( $this->view->filter)){
            foreach( $this->view->filter as $key => $filter){
                if ($this->view->filter[$key]['property']) {
                    $this->view->filter[$key]['property'] = trim($filter['property']);
                    $this->titleHelper->addResource($filter['property']);
                }
                if (($this->view->filter[$key]['value1']) && ($filter['valuetype'] == 'uri') ) {
                    $this->view->filter[$key]['value1'] = trim($filter['value1']);
                    $this->titleHelper->addResource($filter['value1']);
                }
                if (($this->view->filter[$key]['value2']) && ($filter['valuetype'] == 'uri') ) {
                    $this->view->filter[$key]['value2'] = trim($filter['value2']);
                    $this->titleHelper->addResource($filter['value2']);
                }
            }
        }

        $this->view->titleHelper = $this->titleHelper;

        $filter_js = json_encode(is_array($this->view->filter) ? $this->view->filter : array());
        $this->view->headScript()->appendScript('var filtersFromSession = ' . $filter_js.';');

        $this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/filter.js');

        $content = $this->render('filter');
        return $content;
    }

}

