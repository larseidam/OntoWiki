<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: View.php 4235 2009-10-05 12:05:22Z norman.heino $
 */

require_once 'Zend/View/Helper/Placeholder/Registry.php';
require_once 'OntoWiki/Module/Registry.php';

/**
 * OntoWiki view class
 *
 * Subclasses Zend_View in order to cache modules and
 * provide a faster interface to important helpers.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_View extends Zend_View
{    
    /** 
     * OntoWiki application config
     * @var Zend_Config 
     */
    protected $_config = null;
    
    /** 
     * Module cache
     * @var Zend_Cache 
     */
    protected $_moduleCache = null;
    
    /** 
     * The module registry 
     * @var OntoWiki_Module_Registry 
     */
    protected $_moduleRegistry = null;
    
    /** 
     * Translation object
     * @var Zend_Translate 
     */
    protected $_translate = null;
    
    /** 
     * Zend View Placeholder registry
     * @var Zend_View_Helper_Placeholder_Registry 
     */
    protected $_placeholderRegistry = null;
    
    /** 
     * OntoWiki Application
     * @var OntoWiki_Application 
     */
    protected $_owApp = null;
    
    /** 
     * Subview for rendering modules
     * @var OntoWiki_View 
     */
    protected $_moduleView = null;
    
    /**
     * Constructor
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        
        $this->_owApp     = OntoWiki_Application::getInstance();
        $this->_config    = $this->_owApp->config;
        $this->_translate = $this->_owApp->translate;
        
        $this->_moduleRegistry = OntoWiki_Module_Registry::getInstance();
        
        $this->_placeholderRegistry = Zend_View_Helper_Placeholder_Registry::getRegistry();
        
        if ((boolean) $this->_config->cache->modules) {
            if (is_writable($this->_config->cache->path)) {
                // set up module cache
                $frontendOptions = array(
                    'cache_id_prefix' => '_module_'
                );
                $backendOptions = array(
                    'cache_dir' => $this->_config->cache->path
                );
                $this->_moduleCache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
            } else {
                // folder not writable
                $this->_owApp->logger->info("Could not initialize module cache. Cache folder not writable.");
            }
        } else {
            // caching disabled
            $this->_owApp->logger->info("Module cache disabled.");
        }
    }
    
    /**
     * Provides a shortcut to Zend_Translate from within templates.
     *
     * Also tries to cast to string.
     *
     * @param string $key the key for the translation table
     */
    public function _($key)
    {
        return $this->_translate->translate((string) $key);
    }
    
    /**
     * Checks whether a placeholder contains data or view variable exists
     *
     * @param string $name the name of the placeholder or view variable
     */
    public function has($name)
    {
        // check view variables
        if (isset($this->$name) && !empty($this->$name) &&  $this->$name != '') {
            return true;
        }
        
        // check placeholders
        if ($this->_placeholderRegistry->containerExists($name)) {
            $value = $this->_placeholderRegistry->getContainer($name)->getValue();
            
            if (!empty($value) && $value != '') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Clears the cache entry for a specific module or all modules.
     *
     * @param string|null $moduleName If null, all cache for all modules is cleared.
     * @return bool
     */
    public function clearModuleCache($moduleName = null)
    {
        if ($this->_moduleCache) {
            if (null !== $moduleName) {
                return $this->_moduleCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('module', $moduleName));
            }

            return $this->_moduleCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
    }
    
    /**
     * Renders all modules registered for a certain module context.
     *
     * @param string $context The module context whose modules should be rendered
     * @return string
     */
    public function modules($context)
    {
        $modules = '';
        foreach ($this->_moduleRegistry->getModulesForContext($context) as $module) {
            $modules .= $this->module($module['name'], $module, $context);
        }
        
        return $modules;
    }
    
    /**
     * Module view helper.
     *
     * Returns an OntoWiki module either rendered or from cache.
     *
     * Fetches the module from the module registry and renders it into the 
     * window template. If a rendering exists in the local module cache it 
     * is used instead.
     *
     * @param string $moduleName
     * @param array $moduleOptions An associative array of module options.
     *        The following keys can be used:
     *        enabled  – whether the module is enabled or disabled
     *        title    – the module window's title
     *        caching  – whether the module should be cached
     *        priority – priority of the module in the module contexts
     *                   lower number means higher priority
     *        classes  – string of css classes for the module window
     *        id       – a css id for the module window
     * @return string
     */
    public function module($moduleName, $moduleOptions = null, $context = OntoWiki_Module_Registry::DEFAULT_CONTEXT)
    {
        require_once 'OntoWiki/Module/Registry.php';
        $moduleRegistry = OntoWiki_Module_Registry::getInstance();
        
        // no options provided, get them from registry
        if (null === $moduleOptions) {
            $moduleOptions = $moduleRegistry->getModuleConfig($moduleName);
        }
        
        $cssClasses  = isset($moduleOptions['classes']) ? $moduleOptions['classes'] : '';
        $cssId       = isset($moduleOptions['id']) ? $moduleOptions['id'] : '';
        
        $module = $moduleRegistry->getModule($moduleName, $context);
        
        if ($module->shouldShow()) {
            // init module view
            if (null == $this->_moduleView) {
                $this->_moduleView = clone $this;
            }
            $this->_moduleView->clearVars();
            
            // query module's title
            $this->_moduleView->title = $module->getTitle();
            
            // does the module have a message
            // TODO: allow multiple messages
            if (method_exists($module, 'getMessage')) {
                if ($message = $module->getMessage()) {
                    $this->_moduleView->messages = array($message);
                }
            }
            
            // does the module have a menu?
            if (method_exists($module, 'getMenu')) {
                $menu = $module->getMenu();
                $this->_moduleView->menu = $menu->toArray(false, false);
            }
            
            // does the module have a context menu?
            if (method_exists($module, 'getContextMenu')) {
                $contextMenu = $module->getContextMenu();
                if ($contextMenu instanceof OntoWiki_Menu) {
                    $contextMenu = $contextMenu->toArray();
                }
                $this->_moduleView->contextmenu = $contextMenu;
            }
            
            // is caching enabled
            if ($this->_moduleCache and $module->allowCaching()) {
                // get cache id
                $cacheId = md5($module->getCacheId() . $cssClasses . $this->_config->languages->locale);
                
                // cache hit?
                if (!$moduleContent = $this->_moduleCache->load($cacheId)) {
                    
                    // render (expensive) contents
                    $pre = microtime(true);
                    $moduleContent = $module->getContents();
                    $post = ((microtime(true) - $pre) * 1000);
                    $this->_owApp->logger->info("Rendering module '$moduleName': $post ms (cache miss)");
                    
                    // save to cache
                    $this->_moduleCache->save($moduleContent, $cacheId, array('module', $moduleName), $module->getCacheLivetime());
                } else {
                    $this->_owApp->logger->info("Loading module '$moduleName' from cache.");
                }
            } else {
                // caching disabled
                $pre = microtime(true);
                $moduleContent = $module->getContents();
                $post = ((microtime(true) - $pre) * 1000);
                $this->_owApp->logger->info("Rendering module '$moduleName': $post ms (caching disabled)");
            }
            
            // implement tabs
            if (is_array($moduleContent)) {
                // TODO: tabs
                $navigation = array();
                $content    = array();
                
                $i = 0;
                foreach ($moduleContent as $key => $content) {
                    $navigation[$key] = array(
                        'active' => $i++ == 0 ? 'active' : '', 
                        'url'    => '#' . $key, 
                        'name'   => $this->_($key)
                    );
                }
                
                $this->_moduleView->navigation = $navigation;
                $this->_moduleView->content    = $moduleContent;
            } else if (is_string($moduleContent)) {
                $this->_moduleView->content = $moduleContent;
            }
            
            // set variables
            $this->_moduleView->cssClasses = $cssClasses;
            $this->_moduleView->cssId      = $cssId;
            
            // render as window
            $moduleWindow = $this->_moduleView->render('partials/window.phtml');

            return $moduleWindow;
        }
    }
}


