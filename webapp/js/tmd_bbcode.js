//tmd'bb's
(function($)
{
  $.tmdBBCodes = function(o)
  {
    if(o && o.lang)
      message.lang = o.lang;


    var smiliesURL='/pic/smilies/';
    var temp=[], i=0, p=0; temp[i]='';
    var allTextAreas = "#posttext, form[action*='editpost'] textarea, form[action*='edit'] textarea, textarea[name='test'], textarea[name='msg'], textarea[name='body'], textarea[name='descr'], textarea.message, .withBbcode";

    function init() {
      //fashem fonts-uri la oameni)))
      var fontsDropMenu=[], fonts = ['Arial', 'Comic Sans MS', 'Courier New', 'Lucida Console', 'Tahoma', 'Times New Roman', 'Verdana', 'Symbol'];
      for (var i=0; i<fonts.length; i++)
      {
        fontsDropMenu.push({name:fonts[i], openWith:'[font='+fonts[i]+']', closeWith:'[/font]'});
      }

      tmdBbCodes =
      {
          markupSet: [
          {name:'Bold', key:'B', className:'bold', openWith:'[b]', closeWith:'[/b]'},
          {name:'Italic', key:'I', className:'italic', openWith:'[i]', closeWith:'[/i]'},
          {name:'Underline', key:'U', className:'underline', openWith:'[u]', closeWith:'[/u]'},
          {name:'Stroke', className:'stroke', openWith:'[s]', closeWith:'[/s]'},
          {name:'Font', className:'font', openWith:'[font=[![Font]!]]', closeWith:'[/font]', dropMenu : fontsDropMenu},
          {separator:' ' },
          {name:'Center Alignment', className:'center', openWith:'[center]', closeWith:'[/center]'},
          {name:'Right Alignment', className:'right', openWith:'[right]', closeWith:'[/right]'},
          {name:'Preformed text', className:'pre', openWith:'[pre]', closeWith:'[/pre]'},
          {separator:' ' },
          {name:'Size', key:'S', className:'fonts', openWith:'[size=[!['+message('size')+']!]]', closeWith:'[/size]',
          dropMenu :[
            {name:'Size 1', openWith:'[size=1]', closeWith:'[/size]' },
            {name:'Size 2', openWith:'[size=2]', closeWith:'[/size]' },
            {name:'Size 3', openWith:'[size=3]', closeWith:'[/size]' },
            {name:'Size 4', openWith:'[size=4]', closeWith:'[/size]' },
            {name:'Size 5', openWith:'[size=5]', closeWith:'[/size]' },
            {name:'Size 6', openWith:'[size=6]', closeWith:'[/size]' },
            {name:'Size 7', openWith:'[size=7]', closeWith:'[/size]' }
          ]},
          { name:'Colors', key:'K', className:'colors', openWith:'[color=[![Color]!]]', closeWith:'[/color]',
              dropMenu: [
                {openWith:'[color=#FFFFFF]',  closeWith:'[/color]', className:"col1-1" },
                {openWith:'[color=#CCCCCC]',  closeWith:'[/color]', className:"col1-2" },
                {openWith:'[color=#999999]',  closeWith:'[/color]', className:"col1-3" },
                {openWith:'[color=#666666]',  closeWith:'[/color]', className:"col1-4" },
                {openWith:'[color=#333333]',  closeWith:'[/color]', className:"col1-5" },
                {openWith:'[color=#000000]',  closeWith:'[/color]', className:"col1-6" },

                {openWith:'[color=#FF3333]',  closeWith:'[/color]', className:"col2-1" },
                {openWith:'[color=#FF0000]',  closeWith:'[/color]', className:"col2-2" },
                {openWith:'[color=#CC0000]',  closeWith:'[/color]', className:"col2-3" },
                {openWith:'[color=#990000]',  closeWith:'[/color]', className:"col2-4" },
                {openWith:'[color=#660000]',  closeWith:'[/color]', className:"col2-5" },
                {openWith:'[color=#330000]',  closeWith:'[/color]', className:"col2-6" },

                {openWith:'[color=#FFFF99]',  closeWith:'[/color]', className:"col3-1" },
                {openWith:'[color=#FFFF66]',  closeWith:'[/color]', className:"col3-2" },
                {openWith:'[color=#FFCC33]',  closeWith:'[/color]', className:"col3-3" },
                {openWith:'[color=#CC9933]',  closeWith:'[/color]', className:"col3-4" },
                {openWith:'[color=#996633]',  closeWith:'[/color]', className:"col3-5" },
                {openWith:'[color=#663333]',  closeWith:'[/color]', className:"col3-6" },

                {openWith:'[color=#66FF66]',  closeWith:'[/color]', className:"col4-1" },
                {openWith:'[color=#00FF00]',  closeWith:'[/color]', className:"col4-2" },
                {openWith:'[color=#00CC00]',  closeWith:'[/color]', className:"col4-3" },
                {openWith:'[color=#009900]',  closeWith:'[/color]', className:"col4-4" },
                {openWith:'[color=#006600]',  closeWith:'[/color]', className:"col4-5" },
                {openWith:'[color=#003300]',  closeWith:'[/color]', className:"col4-6" },

                {openWith:'[color=#6666FF]',  closeWith:'[/color]', className:"col5-1" },
                {openWith:'[color=#0000FF]',  closeWith:'[/color]', className:"col5-2" },
                {openWith:'[color=#0000CC]',  closeWith:'[/color]', className:"col5-3" },
                {openWith:'[color=#000099]',  closeWith:'[/color]', className:"col5-4" },
                {openWith:'[color=#000066]',  closeWith:'[/color]', className:"col5-5" },
                {openWith:'[color=#000033]',  closeWith:'[/color]', className:"col5-6" },

                {openWith:'[color=#FF66FF]',  closeWith:'[/color]', className:"col6-1" },
                {openWith:'[color=#FF33FF]',  closeWith:'[/color]', className:"col6-2" },
                {openWith:'[color=#CC33CC]',  closeWith:'[/color]', className:"col6-3" },
                {openWith:'[color=#993399]',  closeWith:'[/color]', className:"col6-4" },
                {openWith:'[color=#663366]',  closeWith:'[/color]', className:"col6-5" },
                {openWith:'[color=#330033]',  closeWith:'[/color]', className:"col6-6" }
              ]
              },
          {name:'List item', className:'list', openWith:'[*] ', multilineSupport:true},
            {name:'Smiles', className:'smiles', openWith:function(h){h.textarea.id=h.textarea.id||h.textarea.name; BBsmiles(h.textarea.id); }, closeWith:'',
                    dropMenu:[
                    {name:'smile.gif', openWith:':)'},
                    {name:'cry.gif', openWith:":'-("},
                    {name:'sad.gif', openWith:':('},
                    {name:'grin.gif', openWith:':D'},
                    {name:'confused.gif', openWith:':-/'},

                    {name:'w00t.gif', openWith:':w00t:'},
                    {name:'noexpression.gif', openWith:":|"},
                    {name:'acute.gif', openWith:':acute:'},
                    {name:'annoyed.gif', openWith:':annoyed:'},
                    {name:'look.gif', openWith:':look:'},

                    {name:'airkiss.gif', openWith:':airkiss:'},
                    {name:'alien.gif', openWith:":alien:"},
                    {name:'angel.gif', openWith:':angel:'},
                    {name:'beee.gif', openWith:':beee:'},
                    {name:'ras.gif', openWith:':ras:'},

                    {name:'blink.gif', openWith:':blink:'},
                    {name:'blush.gif', openWith:":blush:"},
                    {name:'boxing.gif', openWith:':boxing:'},
                    {name:'bye.gif', openWith:':bye:'},
                    {name:'down.gif', openWith:':down:'},

                    {name:'fie.gif', openWith:':fie:'},
                    {name:'fist.gif', openWith:":fist:"},
                    {name:'fun.gif', openWith:':fun:'},
                    {name:'geek.gif', openWith:':geek:'},
                    {name:'giveheart2.gif', openWith:':giveheart2:'},

                    {name:'heartbeat.gif', openWith:':heartbeat:'},
                    {name:'hmm.gif', openWith:":hmm:"},
                    {name:'thumbsup_blue.png', openWith:':+1:'},
                    {name:'huh.gif', openWith:':huh:'},
                    {name:'ike.gif', openWith:':ike:'}
                ]},
          {separator:' ' },
          {name:'Picture', key:'P', className:'picture', replaceWith:'[img][![Url]!][/img]'},
          {name:'Iurl', className:'iurl', openWith:'[iurl=[![Url]!]]{}', closeWith:'[/iurl]', placeHolder:'TEXT'},
          {name:'Link', key:'L', className:'link', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder: message('link')},
          {name:'Anchor', className:'anchor', openWith:'[anchor][!['+message('anchor')+':]!][/anchor]'},
          {name:'Anchor Link', className:'anchor_lnk', openWith:'[url=#[!['+message('anchor')+':]!]]', closeWith:'[/url]', placeHolder:'Numele linkului'},
              {name:'Youtube', className:'youtube', openWith:'[yt][![Youtube link:]!]', closeWith:'[/yt]'},
          {separator:' ' },
          {name:'Quotes', openWith:'[quote]', className:'quote', closeWith:'[/quote]'},
          {name:'Code', openWith:'[code]', className:'code', closeWith:'[/code]'},
          {name:'Spoiler', openWith:'[spoiler=[![Spoiler name:]!]]', className:'spoiler', closeWith:'[/spoiler]', placeHolder: message('spoiler')},
          {separator:' ' },
          {name:'Clean', className:"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ""); } },
              {name:'Undo', key:'Z', className:"undo", replaceWith:function(h) { h.textarea.value = wLogBack(h.textarea.value);  } },
              {name:'Redo', key:'Y', className:"redo", replaceWith:function(h) { h.textarea.value = wLogNext(h.textarea.value); }  },
              {name:'Preview', className:"preview" }

        ]
      };//tmdBbCodes


      $(allTextAreas).livequery(function()
        {
          $(this).not('.markItUpEditor').markItUp(tmdBbCodes).show(1, function() //show - pu un fel de callback, nu-mi vine nimic altceva in cap la moment
          {
            markItUpStart();
          });
        });

    }

    //defapt ar fi cool de rescris asta în JS, or de făcut un .php special
    //dar, mai așteptăm cu asta ^__^
    function myPreview(v, e)
    {
      var $t  = $('textarea.markItUpEditor');

        if( $('#prv').length > 0 || $t.val().replace(/\[(.*?)\]/g, "").length < 1 )
          return;

        var $dInfo, $ldngDiv;

        var t = $t.map(function()
        {
          return {w:$(this).width(), h:$(this).height(), c:$(this).offset()};
        }).get(0);


        $(v).hide();
        $ldngDiv = $('<img/>',{'src':'pic/loading2.gif'}).css({'margin-top': '3px'}).insertAfter($(v));

        var val = $t.val();
        $.post("/tags.php", { 'test': val } ).then(function(data) {
            var html = data.replace(/\n/g, '').replace(/.*<p><hr>/g, '').replace(new RegExp("<hr></p>.*", "g" ), '');
            //bagam in <td>, ca altfel nu se init spoilers
            html='<td style="border: 0px; width: '+t.w+'px;">'+html+'</td>';

            $prv = $('<div>',{'id':'prv'}).css({ 'width':t.w, 'height':t.h, 'left':t.c.left, 'top':t.c.top, 'text-align':'left', 'position':'absolute', 'z-index': '88', 'background-color':'#ece9d8', 'border':'solid #A79F72 1px', 'display':'none', 'padding':'5px', 'overflow-y':'auto' }).insertBefore($t.parents('div.markItUp')  ).fadeIn(300).html(html);

            $ldngDiv.fadeOut(300, function()
            {
              $dInfo = $('<div/>').html('<b>&nbsp;&nbsp;&nbsp;'+message('infopreview')+'</b>').hide();
              $pUl = $(this).parents('ul').fadeOut(290).after($dInfo);
              $dInfo.delay(300).fadeIn(600).delay(5000).fadeOut(300, hideHeader);
            });

            $prv.append('<script>initSpoilers(); initIurl(); </script>');


            var hideHeader = function hideHeader()
            {
              $ldngDiv.remove();
              $dInfo.remove();
              $pUl.fadeIn(300);
            };

            var hideAll = function hideAll()
            {
              $prv.remove();
              hideHeader();
              $(v).show();
            };

            var hidePrv = function()
            {
                $(document).one("click", function(e)
                {
                    if ($(e.target).parents('#prv').length > 0)
                    {
                        hidePrv();
                    } else {
                        $prv.fadeOut(300, hideAll);
                    }

                });

                $(document).one("contextmenu", function(e)
                {
                        $prv.fadeOut(300, hideAll);
                        e.preventDefault();
                });

            };
            hidePrv();

        });//dfdPost

    }//myPreview()


    function BBsmiles(here)
    {
      $smilies = $('#smilies');
      if($smilies.length)
      {
        $smilies.show();
        $(document).one('click', function(){ $smilies.hide(); });
        return;
      }

      var h = $('textarea.markItUpEditor').height();

      $iframe = $('<iframe>', {'frameborder':'0', 'width':'180', 'height':(h+11), 'src':'/smilies_popup.php?text=&container=bbIframeSmilies&lang=ro'});
      $smilies = $('<div>',{'id':'smilies', 'style':'position: absolute; z-index: 10001;top: 22px; right: -12px;'}).insertAfter($('.markItUpHeader')).append($iframe);

      $iframe.load(function()
        {
        $iframe.contents().find("div, br, table tr:eq(0)").remove();
        $iframe.contents().find("a").each(function()
            {
                $img=$('img',this);
                $img.attr('width', ($img.attr('width') > 40)? 40: $img.attr('width'));
                $img.attr('height', ($img.attr('height') > 40)? 40: $img.attr('height'));
            }).click(function(e)
            {

                $.markItUp( { replaceWith:$(this).attr('href').replace(/.+IT\(\"/, '').replace(/\"\);/, '') } );

                e.preventDefault();
                $smilies.hide();
            });
            $(document).one('click', function(){ $smilies.hide(); });
        });
    }

    function wLogAdd(h) {
        if (temp[i]!=h && h!="")
        {
            i=i+1; p=i;
            temp[i]=h;
        }
    }

    function wLogBack(h) {
        if (p>0)
        {
            if (p==i) {wLogAdd(h);}
            p=p-1;
            return temp[p];
        }
        if (p==0)
            return temp[p];

        return '';
    }

    function wLogNext(h)
    {
        if (p+1<=i)
        {
            p=p+1;
            return temp[p];
        }
        return h;
    }


      function markItUpStart() {
          $(".markItUpEditor").keypress(function(e) {
            wLogAdd(this.value);
          }).keydown(function(e) {
            if(e.keyCode==8 || e.keyCode==46) {wLogAdd(this.value);}
            if ((e.ctrlKey) && (e.keyCode == 86)) {wLogAdd(this.value);}
          });

          //preview
          $(".markItUpContainer ul li.preview a").click(function(e) {
            myPreview($(this), e);
          });


          //smilies
          $(".markItUpContainer ul li.smiles ul li a").each(function() {
            var sName = $(this).attr('title');
            $(this).attr("style", 'background-image:url('+smiliesURL+sName+'); background-size: auto 20px; background-position: center; background-repeat: no-repeat; padding: 0px;').text('').attr('title','');
          });


          //fashem ca la oameni ^_^
          $('.markItUpContainer li.font ul li a').each(function(i,v)
          {
            $(v).css('font-family', $(v).attr('title'));
          });
      }


      init();

  };

})(jQuery);