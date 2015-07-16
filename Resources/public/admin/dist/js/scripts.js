$(document).ready(function(){
    $('[data-trigger]').click(function(e){
        e.preventDefault();
        var selector = $(this).attr('data-trigger');
        $(selector).trigger('click');
    })
});

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