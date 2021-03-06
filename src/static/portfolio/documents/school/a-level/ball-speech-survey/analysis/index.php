<?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/cache/enabled.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/resources/dargmuesli/base/skeleton.php';
    include_once 'layout/extension/extension.php';

    last_modified(get_page_mod_time());

    $skeletonDescription = 'Auswertung der Umfrage zur Abschlussrede auf dem Abiball 2016 des Friedrichsgymnasiums in Kassel';
    $skeletonFeatures = ['lcl/ext/js', 'pkg/chrt/mjs'];
    $skeletonContent = '
    <div class="canvaswrapper">
        <canvas id="barChart">
        </canvas>
    </div>
    <p>
        <a class="waves-effect waves-light btn" href="../" title="Abiballrede - Umfrage">
            <i class="material-icons left">
                chevron_left
            </i>
            Zurück zur Umfrage
        </a>
    </p>';

    output_html($skeletonDescription, $skeletonContent, $skeletonFeatures);
