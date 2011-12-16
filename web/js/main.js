$(document).ready(function(){
    $('#edit_movie_form').submit(onSubmitMovie);
    
    jQuery("#posters").nivoSlider({
        effect:"random",
        slices:15,
        boxCols:8,
        boxRows:4,
        animSpeed:500,
        pauseTime:3000,
        startSlide:0,
        directionNav:false,
        directionNavHide:false,
        controlNav:false,
        controlNavThumbs:false,
        controlNavThumbsFromRel:false,
        keyboardNav:false,
        pauseOnHover:true,
        manualAdvance:false
    });
    
    $( "#form_query" ).autocomplete({
        delay: 100,
        source: onAutoCompleteSearch,
        minLength: 2
    });
    
    $("#movie_title").autocomplete({
        source: onAutoCompleteTmdb,
        minLength:3,
        select: function( event, ui ) {
            if (ui.item) {
                $('#movie_movieDbLink').val(ui.item.key);
            }
        }
    });
    
});

function onAutoCompleteSearch(request, response) {
    var term = request.term;
            
    $.getJSON("http://localhost/moviedb/web/app_dev.php/search/title/" + term, function(data, status, xhr) {
        response(data);
    });
}

function onAutoCompleteTmdb(request, response) {
    var term = request.term;
    
    $.getJSON("http://localhost/moviedb/web/app_dev.php/search/movie/" + term + "/true", function(data, status, xhr) {
        if (data != '["Nothing found."]')
            response(data); 
    });
}
 
function onSubmitMovie() {
    var value = $('#movie_title').val();    
    var dbId = $('#movie_movieDbLink').val();

    if (dbId > 0) {
        return true;
    }
    
    if (dbId < 0) {
        $('#movie_movieDbLink').val('');
        return true;
    }
    
    $.getJSON("http://localhost/moviedb/web/app_dev.php/search/movie/" + value, onGetTmdbResult);
    
    return false;
}

function chooseMovieDbLink() {
    var hidden = $('#movie_movieDbLink');
    var elem = $(this);
    var tmdbId = elem.attr('id');
    
    hidden.val(tmdbId);
    
    $('#edit_movie_form').submit();
 
    return false;
}

function onGetTmdbResult(json) {
    $('#movie_db_result').html('');
    
    var ul = $("<ul class='result_list'></ul>");
    var data = eval('(' + json + ')');
            
    $.each(data,function(index, value) {
        var li = $("<li id='" + value.id + "'><a href='#'>" + value.name + "</a></li>");
        li.click(chooseMovieDbLink);
        li.appendTo(ul);
    });
    
    var noneLi = $('<li id="-1"><a href="#">Kein Movie DB Link</a></li>');
    noneLi.appendTo(ul);

    ul.appendTo($('#movie_db_result'));
    $('#movie_db_result').slideDown();
}
