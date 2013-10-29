<?php
$this->breadcrumbs=array(
	'Pwi',
);?>

<?php
    global $_assetsUrl;
    $_assetsUrl = false;
 
    function getAssetsUrl()
    {
        global $_assetsUrl;
        if ( !$_assetsUrl )
            $_assetsUrl = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias('application.views.pwi.images') );
        return $_assetsUrl;
    }

    function get_app_url()
    {
        echo Yii::app()->request->baseUrl;
    }

    if (Yii::app()->user->allowedAccess('task', 'Administrator'))
        echo '<script src="' . Yii::app()->request->baseUrl . '/js/pwi-with-buses.js" type="text/javascript"></script>';
    else
        echo '<script src="' . Yii::app()->request->baseUrl . '/js/pwi.js" type="text/javascript"></script>';
?>

    <link rel="stylesheet" type="text/css" href="<?php get_app_url(); ?>/css/pwi.css" />

    <div id="rtpi-title" style="font-size:0.83em"><h1 style="margin-top:0; color:#439DC2;font-size:14.6167px; margin-bottom:10px;">Real Time Bus Information</h1></div>

    <div class="mapping">
        <div id="searchbar">
            <form id="searchForm" action="" onsubmit="search(this.s.value); return false;" style="width:800px">
            <label for="s"><!-- route, postcode, name, atco, naptan --></label>
            <input type="text" value="" size="40" name="s" id="s" />
            <input type="submit" id="searchSubmit" value="Search" />
            </form>
        </div>
        <div class="leftcol">
            <div class="menupane curved">
                <div id="results" class="list">
                    <div style="font-size:medium">
                        <br/>Enter a search term in the box above then press enter or click on the Search button.<br/><br/>You can search for:<ul><li>a bus route code</li><li>a postcode</li><li>part of a stop name</li><li>a bus stop code<br/>(eg. "rdgagat")</li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="rightcol">
            <div id="mstatus" class="stattxt"></div>
            <div id="mstatus2" class="stattxt"></div>
            <div id="overlay" class="curved">
                <div class="ocontent curved">
                    <div class="paneheader">
                        <p style="float:left">Departures</p>
                        <div id="close" class="button"><a title="Close" href="javascript:hideOverlay()"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
                        <div id="popout" class="button"></div>
                    </div>
                    <div id="depinfo"></div>
                    <div id="messages"></div>
                    <div class="panefooter">
                        <div id="dstatus" class="stattxt"></div>
                        <div id="approaching" class="approaching"></div>
                    </div>
                </div>
            </div>
            <div id="map">Loading map...</div>
        </div>
    </div>
    <noscript><b>JavaScript must be enabled in your browser for this page to function correctly.</b></noscript>
