<?php
  include 'df.php' ;
  
  if(empty($mceType) || $mceType <> "simple"){
    $mceType = "advanced" ;
  }
?>
<script language="javascript" type="text/javascript" src="<?php echo(compFolder()) ?>/editor/tiny_mce_src.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "<?php echo($mceType) ?>",
        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        file_browser_callback : 'mce_imageBrowse',

        // Skin options
        skin : "o2k7",
        // skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        // content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js",

        // Replace values for the template plugin
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
});

  function fileBrowserCallBack(field_name, url, type, win) {
  }
  
  function setupContentCallback(editor_id, node) {    
    var nID = 0 ;
    var va = editor_id.split("_") ;
    if(va.length >= 3){
      nID = va[2] ;
    }
    editor = document.getElementById(editor_id);
    if (editor){ 
      eval("window.mce"+nID+"=editor") ;
    }
  }
</script>