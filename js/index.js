var fabButton = document.getElementById('fab-upload');
var fab = new Tether({
    element: fabButton,
    target: document.body,
    attachment: 'bottom right',
    targetAttachment: 'bottom right',
    offset: '35px 35px',
    targetModifier: 'visible'
});


var $header = $('#header');

$('.mdl-layout__content').scroll(function () {
    if ($(this).scrollTop() > 10) {
        $header.css({backgroundColor: '#011b28', boxShadow: '0px 2px 2px 0px rgba(0, 0, 0, 0.14), 0px 3px 1px -2px rgba(0, 0, 0, 0.2), 0px 1px 5px 0px rgba(0, 0, 0, 0.12)'});
    } else {
        $header.css({backgroundColor: 'transparent', boxShadow: 'none'});
    }
});


function refreshUI() {
    var width = $(window).width();
    if (width > 1200) {
        fab.setOptions({
            element: fabButton,
            target: document.body,
            attachment: 'bottom right',
            targetAttachment: 'bottom right',
            offset: '40px '+ (width/2 - 17) +'px',
            targetModifier: 'visible'
        })
    } else if (width > 462) {
        fab.setOptions({
            element: fabButton,
            target: document.body,
            attachment: 'bottom right',
            targetAttachment: 'bottom right',
            offset: '35px 35px',
            targetModifier: 'visible'
        })
    } else if (width > 0) {
        fab.setOptions({
            element: fabButton,
            target: document.body,
            attachment: 'bottom right',
            targetAttachment: 'bottom right',
            offset: '20px 20px',
            targetModifier: 'visible'
        })
    }
}

function showCookieMessage() {
    var snackBar = document.querySelector('#snackbar-cookie');
    // TODO: Show snackbar with cookie message
}

function loadEntries() {

    $.ajax({
        url: 'server/server.php',
        type: 'POST',
        data: { mode: 'get_list'},
        success: function (json) {
            var arr = $.parseJSON(json);
            var newhtml = '';

            if (arr.length === 0) {
                newhtml = '<div class="mdl-cell mdl-cell--4-col  card-lesson mdl-card mdl-shadow--2dp">' +
                    '<div class="mdl-card__title"><h2 class="mdl-card__title-text">No lessons available</h2></div>' +
                '<div class="mdl-card__supporting-text mdl-card--expand">' +
                    'You can add new lessons by pressing the upload button on the bottom of the page.' +
                '</div><div class="mdl-card__actions mdl-card--border"><div class="mdl-layout-spacer"></div>' +
                    '<a class="mdl-button mdl-js-button mdl-js-ripple-effect">ok</a></div></div>';
            } else {
                for(var i=0; i < arr.length; i++){

                    newhtml += '<div class="mdl-cell mdl-cell--4-col  card-lesson mdl-card mdl-shadow--2dp">' +
                        '<div class="mdl-card__title">' +
                        '<h2 class="mdl-card__title-text">'+ arr[i].name +'</h2></div>'+
                        '<div class="mdl-card__supporting-text mdl-card--expand">' +
                        '<div class="row"><i class="material-icons">language</i><span>' + arr[i].lang1 + ' - ' + arr[i].lang2 + '</span></div><br>' +
                        '<div class="row"><i class="material-icons">translate</i><span>'+arr[i].line_cnt+' vocables</span></div></div>' +
                        '<div class="mdl-card__actions mdl-card--border"><div class="mdl-layout-spacer"></div>'+
                        '<a id="'+ i +'" onclick="gridItemClicked(this)" class="mdl-button mdl-js-button mdl-js-ripple-effect">Start lesson</a></div>' +
                        '<div class="mdl-card__menu">' +
                        '<button id="menu" class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect"><i class="material-icons">more_vert</i></button>'+
                        '<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="menu"><li class="mdl-menu__item">Edit</li><li class="mdl-menu__item">Delete</li></ul></div></div>';
                }
            }
            
            document.getElementById('grid').innerHTML = newhtml;
        }
    });
}



$(document).ready(function () {
    refreshUI();
    Tether.position();
    //showCookieMessage();
    loadEntries();

});

// action handlers ///////////////////
$(window).resize(function (event) {
    refreshUI();
});