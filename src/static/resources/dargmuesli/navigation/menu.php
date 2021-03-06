<?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/filesystem/directory.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/navigation/translation.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/numbers/counter.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/url/rootpointer.php';

    function get_menu_html()
    {
        // Link to included variables
        global $rootPointerInteger;

        $requestUriWithoutQuery = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $currentFolder = basename($requestUriWithoutQuery);
        $parentFolder = basename(dirname($requestUriWithoutQuery));
        $isErrorPage = isset($_GET['errorCode']);

        // Exception for welcome page
        $linkPrefix = ($rootPointerInteger == 0) ? '/portfolio/' : '../';

        // Initialize output string
        $menuHtml = '<ul class="sidenav sidenav-fixed z-depth-0" id="nav-mobile" itemscope itemtype="https://schema.org/SiteNavigationElement">';

        if ($isErrorPage) {

            // Display welcome and error link on error pages
            $menuHtml .= get_menu_html_item(get_navigation_translation('welcome'), $_SERVER['SERVER_ROOT_URL'])
                        .get_menu_html_item(get_navigation_translation('error'), $_SERVER['SERVER_ROOT_URL'].$_SERVER['REQUEST_URI'].'#!', array('active'));
        } elseif ($rootPointerInteger == 0) {

            // Display active welcome link on welcome page
            $menuHtml .= get_menu_html_item(get_navigation_translation('welcome'), '#!', array('active'));
        } elseif ($rootPointerInteger == 1 && in_array($currentFolder, array('imprint', 'portfolio', 'sitemap'))) {

            // Display welcome and imprint, portfolio or sitemap link for imprint and sitemap page
            $menuHtml .= get_menu_html_item(get_navigation_translation('welcome'), '../')
                        .get_menu_html_item(get_navigation_translation($currentFolder), '../'.$currentFolder.'/#!', array('active'));
        } elseif ($rootPointerInteger == 2) {

            // Display inactive welcome link on portfolio's direct subfolders
            $menuHtml .= get_menu_html_item(get_navigation_translation('welcome'), '../../');
        }

        // Create default folder navigation
        if (!$isErrorPage && $rootPointerInteger != 1) {

            // Scan parent directory
            $searchFolder = null;

            if ($rootPointerInteger == 0) {
                $searchFolder = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'portfolio';
            } else {
                $searchFolder = dirname($_SERVER['DOCUMENT_ROOT'].$requestUriWithoutQuery);
            }

            $directoryArray = get_web_directory_array($searchFolder, array($searchFolder.DIRECTORY_SEPARATOR.$currentFolder.DIRECTORY_SEPARATOR));
            $hasSubfolders = false;

            foreach ($directoryArray as $folderName => $subFolders) {
                if (count($subFolders) > 0) {
                    $hasSubfolders = true;
                    break;
                }
            }

            // Scan parent directory or display directory-up link (for all levels above two)
            if (!$hasSubfolders && $rootPointerInteger != 0) {
                $directoryArray = get_web_directory_array(dirname($searchFolder), array(dirname($searchFolder).DIRECTORY_SEPARATOR.$parentFolder.DIRECTORY_SEPARATOR));
            } elseif ($rootPointerInteger > 2) {
                $menuHtml .= get_menu_html_item(get_navigation_translation('up'), '../', array('center-align'), 'arrow_upward');
            }

            $directoryArrayOrdered = array();
            $directoryArraySublevelOrdered = array();

            // Validate directory array
            if (is_array($directoryArray)) {

                // Iterate over all folders
                foreach ($directoryArray as $folderName => $subFolders) {

                    // Assign folder name to translated folder name
                    $directoryArrayOrdered[get_navigation_translation($folderName)] = $folderName;

                    if (count($subFolders) > 0) {

                        // Iterate over all subfolders in current folder
                        foreach ($subFolders as $folderNameSublevel => $subsubFolders) {

                            // Assign subfolder name to translated subfolder name
                            $directoryArraySublevelOrdered[get_navigation_translation($folderNameSublevel)] = $folderNameSublevel;
                        }

                        // Sort subfolder names' translation alphabetically
                        ksort($directoryArraySublevelOrdered);
                    }
                }
            }

            // Sort folder names' translation alphabetically
            ksort($directoryArrayOrdered);

            if ($rootPointerInteger != 1 && !$isErrorPage) {

                // Iterate over parallel directories
                foreach ($directoryArrayOrdered as $nameTranslated => $nameOriginal) {

                    // Check if there are subdirectories to display for current directory
                    if ($nameOriginal != $currentFolder && $nameOriginal != $parentFolder) {

                        // Display parallel folder without subdirectories
                        if ($hasSubfolders || $rootPointerInteger == 0) {
                            $menuHtml .= '
                            <li itemscope itemtype="https://schema.org/ListItem">
                                <a class="waves-effect waves-primary" itemprop="item" href="'.$linkPrefix.$nameOriginal.'/" title="'.$nameTranslated.'">
                                    <span class="truncate" itemprop="name">
                                        '.$nameTranslated.'
                                    </span>
                                </a>
                                <meta itemprop="position" content="'.use_counter().'">';
                        } else {
                            $menuHtml .= '
                            <li itemscope itemtype="https://schema.org/ListItem">
                                <a class="waves-effect waves-primary" itemprop="item" href="'.$linkPrefix.$linkPrefix.$nameOriginal.'/" title="'.$nameTranslated.'">
                                    <span class="truncate" itemprop="name">
                                        '.$nameTranslated.'
                                    </span>
                                </a>
                                <meta itemprop="position" content="'.use_counter().'">';
                        }
                    } else {

                        // Display parallel folder with subdirectories and check if it is the last-level case
                        // (the parallel folder is inactive then, active otherwise)
                        if ($nameOriginal == $parentFolder) {
                            $menuHtml .= '
                            <li itemscope itemtype="https://schema.org/ListItem">
                                <a class="waves-effect waves-primary" itemprop="item" href="'.$linkPrefix.$linkPrefix.$nameOriginal.'/" title="'.$nameTranslated.'">
                                    <span class="truncate" itemprop="name">
                                        '.$nameTranslated.'
                                    </span>
                                </a>
                                <meta itemprop="position" content="'.use_counter().'">
                                <ul>';
                        } else {
                            $menuHtml .= '
                            <li itemscope itemtype="https://schema.org/ListItem">
                                <a class="active waves-effect waves-primary" itemprop="item" href="'.$linkPrefix.$nameOriginal.'/#!" title="'.$nameTranslated.'">
                                    <span class="truncate" itemprop="name">
                                        '.$nameTranslated.'
                                    </span>
                                </a>
                                <meta itemprop="position" content="'.use_counter().'">
                                <ul>';
                        }

                        // Iterate over subdirectories
                        foreach ($directoryArraySublevelOrdered as $nameTranslatedSublevel => $nameOriginalSublevel) {
                            if ($hasSubfolders) {

                                // Display selected folder's subfolders
                                $menuHtml .= get_menu_html_item($nameTranslatedSublevel, $nameOriginalSublevel.'/');
                            } else {
                                if ($nameOriginalSublevel == $currentFolder) {

                                    // Display selected folder's subfolders
                                    $menuHtml .= get_menu_html_item($nameTranslatedSublevel, $linkPrefix.$nameOriginalSublevel.'/#!', array('active'));
                                } else {

                                    // Display selected folder's subfolders
                                    $menuHtml .= get_menu_html_item($nameTranslatedSublevel, $linkPrefix.$nameOriginalSublevel.'/');
                                }
                            }
                        }

                        $menuHtml .= '
                        </ul>';
                    }

                    $menuHtml .= '
                    </li>';
                }
            }
        }

        $menuHtml .= '
        </ul>';

        return $menuHtml;
    }

    function get_menu_html_item($name, $link, $classes = null, $icon = null)
    {
        $classes = (is_array($classes)) ? join(' ', $classes).' ' : '';

        $menutHtmlItem = '
        <li itemscope itemtype="https://schema.org/ListItem">
            <a class="'.$classes.'waves-effect waves-primary" itemprop="url" href="'.$link.'" title="'.$name.'">
                <span class="truncate" itemprop="name">';

        if ($icon) {
            $menutHtmlItem .= '
            <i class="material-icons">
                '.$icon.'
            </i>';
        } else {
            $menutHtmlItem .= '
                '.$name;
        }

        $menutHtmlItem .= '
                    </span>
                </a>
            <meta itemprop="position" content="'.use_counter().'">
        </li>';

        return get_indented_ml($menutHtmlItem);
    }
