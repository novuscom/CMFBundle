$(document).ready(function(){
    $('[data-trigger]').click(function(e){
        e.preventDefault();
        var selector = $(this).attr('data-trigger');
        $(selector).trigger('click');
    })
});

$(document).ready(function () {
    $('[data-delete]').click(function (e) {
        if (!confirm('Вы действительно хотите удалить этот материал?'))
            e.preventDefault();
        else
            return true

    });
    addFieldInCollection();
    propertyImages();
    TinyMCEStart('.editor', null);

    //$('.fancybox').fancybox();
    //imageReplace();
    //imageDelete();
    /*$('[data-translit]').liTranslit({
     elAlias: $('[data-translit-alias]'),
     reg: '"ё"="yo"," "="-","й"="i"'
     //status:false
     });*/
});

function imageReplace() {
    $('body').on('click', '.file-replace', function (e) {
        e.preventDefault();

    })
}

function imageDelete() {

}

function propertyImages() {
    $('.files-property').each(function (i, o) {
        var attr = $(o).attr('data-data');
        //console.log(attr);
        if (attr !== undefined) {
            var data = $.parseJSON(attr);
            console.log(data);
            var parent = $(o).parents('.form-group:eq(0)');
            var cnt = parent.find('.cmf-form-collection');

            if (data['files'] !== undefined) {
                cnt.prepend('<div class="images"></div>');
                var images = cnt.find('.images');
                $.each(data['files'], function (i, o) {
                    var img =
                        '<div class="image"><a href="' + o['original_path'] + '" target="_blank"><img alt="" src="' + o['path'] + '" /></a>' +
                        '<ul>' +
                        '<li><a href="#" class="file-replace" data-file-id="' + o['file_id'] + '" data-property-id="' + o['property_id'] + '">заменить</a></li>' +
                        '<li><a href="#" class="file-delete">удалить</a></li>' +
                        '</ul>' +
                        '</div>';
                    //console.log(img);
                    images.append(img);
                })
            }
        }
    })
}

function addFieldInCollection() {
    $('.cmf-form-collection__button').click(function (e) {
        e.preventDefault();
        var button = $(this);
        var parent = button.parents('.cmf-form-collection');
        var num = parent.find('.cmf-form-collection__list .cmf-form-collection__item').length;
        var prototype = button.attr('data-prototype');
        var newField = prototype.replace(/__name__/g, num);
        var tpl = parent.find('.cmf-form-collection__template').clone();
        tpl.find('.cmf-form-collection__label').html('<label>' + num + '</label>');
        tpl.find('.cmf-form-collection__field').prepend(newField);
        tpl.removeClass('cmf-form-collection__template').show();
        // Здесь обработка
        parent.find('.cmf-form-collection__list').append(tpl);
    });
    $('body').on('click', '.cmf-form-collection__button-remove', function (e) {
        e.preventDefault();
        var button = $(this);
        var parent = button.parents('.cmf-form-collection__item');
        parent.remove();
    })
}


function TinyMCEStart(elem, mode){
    var plugins = [];
    if (mode == 'extreme'){
        plugins = [ "advlist anchor autolink autoresize autosave charmap code contextmenu directionality ",
            "emoticons fullscreen hr image insertdatetime layer legacyoutput",
            "link lists media nonbreaking noneditable pagebreak paste preview print save searchreplace",
            "tabfocus table textcolor visualblocks visualchars wordcount"]
    }
    tinymce.init({selector: elem,
        theme: "modern",
        language : 'ru',
        height : 500,
        //content_css: "css/style.css",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen"
            //"insertdatetime media table contextmenu paste moxiemanager"
        ],
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        /*style_formats: [
         {title: 'Header 2', block: 'h2', classes: 'page-header'},
         {title: 'Header 3', block: 'h3', classes: 'page-header'},
         {title: 'Header 4', block: 'h4', classes: 'page-header'},
         {title: 'Header 5', block: 'h5', classes: 'page-header'},
         {title: 'Header 6', block: 'h6', classes: 'page-header'},
         {title: 'Bold text', inline: 'b'},
         {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
         {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
         {title: 'Example 1', inline: 'span', classes: 'example1'},
         {title: 'Example 2', inline: 'span', classes: 'example2'},
         {title: 'Table styles'},
         {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'} //blockquote
         ]*/
    });
}