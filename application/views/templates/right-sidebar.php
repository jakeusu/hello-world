<script src="<?php echo asset_base_url()?>/libs/jquery.nicescroll.min.js" type="text/javascript"></script>
<script src="<?php echo asset_base_url()?>/libs/jquery.timeago.min.js" type="text/javascript"></script>
<script src="<?php echo asset_base_url()?>/libs/bootstrap.min.js" type="text/javascript"></script>

<script src="<?php echo asset_base_url()?>/libs/quickblox.min.js" type="text/javascript"></script>

<script src="<?php echo asset_base_url()?>/js/bootstrap-dialog.min.js" type="text/javascript"></script>
<script src="<?php echo asset_base_url()?>/css/bootstrap-dialog.min.css" type="text/css"></script>
<script src="<?php echo asset_base_url()?>/libs/bootstrap.min.css" type="text/css"></script>

<script src="<?php echo asset_base_url()?>/js/config.js" type="text/javascript"></script>
<script>
  var g_users;//group chat members
  var g_ids;
  var p_users;//private chat members
  var p_ids;
	function createChat(type) {
    var title = "Create New ";
    $(".sidepanel").css("display", "none");
    if (type == 1) {
        title += "1:1 Chat";
        BootstrapDialog.show({
            type: BootstrapDialog.TYPE_PRIMARY,
            title: title,
            message: '<div id="channel_edit">'+
                      '<label class="d-label">'+
                        '<span>CHAT WITH</span>'+
                        '<div class="row canvas">'+
                          '<input type="text" placeholder="Search email" id="add_email" onkeyup="handle(event, this)">'+
                          '<button class="ob pull-right" onclick="add_non_user_to_private()" style="position:absolute;top:5px;right:5px;">add</button>'+
                        '</div>'+
                      '</label>'+

                      '<ul id="selected" class="row setting-item">'+
                      '</ul>'+

                      '<ul id="contacts" class="scrollbar row setting-item">'+
                      '</ul>'+
                      '</div>',
            buttons: [{
                label: 'Cancel',
                action: function(dialogRef){
                    dialogRef.close();
                }
            }, {
                label: 'Create Chat',
                cssClass: 'btn-primary',
                icon: 'glyphicon glyphicon-send font-10',
                autospin: true,
                action: function(dialogRef){
                  var params = {
                    type: 1,
                    name: "Private"
                  };

                  QB.chat.dialog.create(params, function(err, createdDialog) {
                    if (err) {
                      console.log(err);
                      alert(err);
                    } else {
                      var occupants = [];
                      var sendEmail = "";
                      if(p_ids == 1) sendEmail = p_users;
                      if(sendEmail !== ""){
                          $.ajax({
                             url: site_url + "users/invite/"+ 3 + "/" + sendEmail.replace("@", "%40") + "/<?php echo isset($page)?$page:'4';?>",
                             data: {
                                email: sendEmail
                             },
                             success: function(data) {
                                          mydata = data.split("\n");
                                          occupants.push([sendEmail, mydata[0]]);   
                                          $.ajax({
                                            url: site_url + 'chat/newChat',
                                            data: {
                                              did: createdDialog._id,
                                              jid: createdDialog.xmpp_room_jid,
                                              type: <?= CHAT_TYPE_PRIVATE?>,
                                              dname: "Private",
                                              ddesc: "1:1 chat",
                                              occupants: occupants
                                            },
                                            success: function(data) {
                                              if (data == "new"){
                                                location.href = site_url + 'chat/channel/' + createdDialog._id;
                                              }
                                              else{
                                                location.href = site_url + 'chat/channel/' + data;
                                              }
                                            
                                            },
                                            type: 'POST'
                                          });


                            },
                           type: 'POST'
                          });                             
                      }
                      else{
                        occupants.push([$("#selected li").data("email"), $("#selected li").data("uid")]);
                        $.ajax({
                           url: site_url + 'chat/newChat',
                           data: {
                              did: createdDialog._id,
                              jid: createdDialog.xmpp_room_jid,
                              type: <?= CHAT_TYPE_PRIVATE?>,
                              dname: "Private",
                              ddesc: "1:1 chat",
                              occupants: occupants
                           },
                           success: function(data) {
                              if (data == "new"){
                                location.href = site_url + 'chat/channel/' + createdDialog._id;
                              }
                              else{
                                location.href = site_url + 'chat/channel/' + data;
                              }
                           },
                           type: 'POST'
                        });
                      }                     
                                  
                        
                    }
                  });
                }
            }]
        });
        
        getUsers('', function(data) {
          buildUsersHTML(data, '', 1);
        });
    } else {
        title += "Group Chat";
        g_users = [];
        g_ids = [];
        BootstrapDialog.show({
            type: BootstrapDialog.TYPE_PRIMARY,
            title: title,
            message: '<div id="channel_edit">'+
                     '<label class="d-label">'+
                        '<span>DETAILS</span>'+
                        '<input type="text" placeholder="Group chat name" id="groupname" onkeyup="enterhandle(event, this)">'+
                      '</label>'+
                      '<label class="d-label">'+
                        '<span>ADD MEMBERS</span>'+
                        '<div class="row canvas">'+
                        '<input type="text" placeholder="Search email" id="add_email" onkeyup="handle(event, this)">'+
                        '<button class="ob pull-right" onclick="add_non_user()" style="position:absolute;top:5px;right:5px;">add</button>'+
                        '</div>'+
                      '</label>'+

                      '<ul id="selected" class="row setting-item">'+
                      '</ul>'+

                      '<ul id="contacts" class="scrollbar row setting-item">'+
                      '</ul>'+
                      '</div>',
            buttons: [{
                label: 'Cancel',
                action: function(dialogRef){
                    dialogRef.close();
                }
            }, {
                label: 'Create Chat',
                cssClass: 'btn-primary',
                icon: 'glyphicon glyphicon-send font-10',
                autospin: true,                
                action: function(dialogRef) {
                  
                  if($("#groupname").val() === ""){
                    alert("Groupname is empty!");
                    return;                    
                  }

                  if(g_users.length == 0){
                    alert("You didn't select users.");
                    return;
                  }

                  var params = {
                    type: 1,
                    name: $("#groupname").val()
                  };
                  QB.chat.dialog.create(params, function(err, createdDialog) {
                    if (err) {
                      console.log(err);
                      alert(err);
                    } else {
                      var occupants = [];
                      var sendEmail = "";
                      for(var i=0; i<g_ids.length; i++){
                        if(g_ids[i] == 1){
                          if(sendEmail !== "") sendEmail += ",";
                          sendEmail += g_users[i];
                        }
                      }
                      if(sendEmail !== ""){
                          $.ajax({
                             url: site_url + "users/invite/"+ 3 + "/" + sendEmail.replace(/@/g, "%40").replace(/,/g, "%2c") + "/<?php echo isset($page)?$page:'4';?>",
                             data: {},
                             success: function(data) {
                                          mydata = data.split("\n");
                                          var e = sendEmail.split(",");
                                          var i = mydata[0].split("/");
                                          for(var k=0; k<e.length; k++){
                                            occupants.push([e[k], i[k]]);  
                                          }                                          
                                          $("#selected li").each(function(){
                                            if($(this).data("uid") == 1) return;
                                            occupants.push([$(this).data("email"), $(this).data("uid")]);
                                          });
                                          //alert(JSON.stringify(occupants));
                                          $.ajax({
                                             url: site_url + 'chat/newChat',
                                             data: {
                                                did: createdDialog._id,
                                                jid: createdDialog.xmpp_room_jid,
                                                type: <?= CHAT_TYPE_GROUP?>,
                                                dname: $("#groupname").val(),
                                                ddesc: $("#groupdesc").val(),
                                                occupants: occupants
                                             },
                                             success: function(data) {
                                                if (data != "exist"){
                                                  location.reload();
                                                }
                                                else
                                                  alert("The chat is already exist.");
                                             },
                                             type: 'POST'
                                        });


                            },
                           type: 'POST'
                          });                             
                      }else{
                        $("#selected li").each(function(){
                          occupants.push([$(this).data("email"), $(this).data("uid")]);
                        });
                        if(g_users.length == 0){
                          alert('You did not select anyone.');
                          return;
                        }
                        $.ajax({
                           url: site_url + 'chat/newChat',
                           data: {
                              did: createdDialog._id,
                              jid: createdDialog.xmpp_room_jid,
                              type: <?= CHAT_TYPE_GROUP?>,
                              dname: $("#groupname").val(),
                              ddesc: $("#groupdesc").val(),
                              occupants: occupants
                           },
                           success: function(data) {
                              if (data == "new"){
                                location.href = site_url + 'chat/channel/' + createdDialog._id;
                              }
                              else{
                                location.href = site_url + 'chat/channel/' + data;
                              }
                           },
                           type: 'POST'
                      });
                      }
                      
                      
                    }
                  });
                }
            }]
        });

        getUsers('', function(data) {
          buildUsersHTML(data, '', 2);
        });

    }  
  }

  function check_memberfield(email){
    var emails = email.split(",");
    var str;
    for(var i=0; i<emails.length; i++){
      str = emails[i];
      if(!validateEmail(str)){
        alert(str + " is invalid email.");
        return false;
      }
    }
    return true;
  }

  function validateEmail(email) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
  }

    function addMember(did, userIDs) {
        g_users = [];
        g_ids = [];
        title = "Add New Members";
        BootstrapDialog.show({
            type: BootstrapDialog.TYPE_PRIMARY,
            title: title,
            message: '<div id="channel_edit">'+
                      '<label class="d-label">'+
                        '<span>ADD MEMBERS</span>'+
                          '<div class="row canvas">'+
                          '<input type="text" placeholder="Enter email" id="add_email" onkeyup="handle(event, this)">'+
                          '<button class="ob pull-right" onclick="add_non_user()" style="position:absolute;top:5px;right:5px;">add</button>'+
                          '</div>'+
                      '</label>'+

                      '<ul id="selected" class="row setting-item">'+
                      '</ul>'+

                      '<ul id="contacts" class="scrollbar row setting-item">'+
                      '</ul>'+
                      '</div>',
            buttons: [{
                label: 'Cancel',
                action: function(dialogRef){
                    dialogRef.close();
                }
            }, {
                label: 'Send Invite',
                cssClass: 'btn-primary',
                icon: 'glyphicon glyphicon-send font-10',
                autospin: true,                
                action: function(dialogRef) {  
                  if(g_users.length == 0){
                    alert("You didn't select users.");
                    return;
                  }

                  var occupants = [];
                  var sendEmail = "";
                  for(var i=0; i<g_ids.length; i++){
                    if(g_ids[i] == 1){
                      if(sendEmail !== "") sendEmail += ",";
                      sendEmail += g_users[i];
                    }
                  }
                  if(sendEmail !== ""){
                    $.ajax({
                                 url: site_url + "users/invite/"+ 2 + "/" + sendEmail.replace("@", "%40") + "/<?php echo isset($page)?$page:'4';?>",
                                 data: {
                                    email: sendEmail
                                 },
                                 success: function(data) {
                                    mydata = data.split("\n");
                                    var e = sendEmail.split(",");
                                    var i = mydata[0].split("/");
                                    for(var k=0; k<e.length; k++){
                                      occupants.push([e[k], i[k]]);  
                                    }                                          
                                    $("#selected li").each(function(){
                                      if($(this).data("uid") == 1) return;
                                      occupants.push([$(this).data("email"), $(this).data("uid")]);
                                    });
                                    
                                    $.ajax({
                                         url: site_url + 'chat/addMember',
                                         data: {
                                            did: did,
                                            occupants: occupants
                                         },
                                         success: function(data) {
                                            if (data == "success")
                                              location.reload();
                                            else
                                              alert(data);
                                         },
                                         type: 'POST'
                                    });
                                  },
                                 type: 'POST'
                            });
                  }
                  else{
                    $("#selected li").each(function(){
                      occupants.push([$(this).data("email"), $(this).data("uid")]);
                    });
                    if(occupants.length == 0){
                      alert("You selected nothing.");
                      return;
                    }

                                    
                    $.ajax({
                         url: site_url + 'chat/addMember',
                         data: {
                            did: did,
                            occupants: occupants
                         },
                         success: function(data) {
                            if (data == "success")
                              location.reload();
                            else
                              alert(data);
                         },
                         type: 'POST'
                    });
                  }

                  
                }
            }]
        });

        getUsers('', function(data) {
          buildUsersHTML(data, '', 2, userIDs);
        });
    }

    $(".chat_with_text").on('input', function(){
        var text = $(this).val();
        $(".contact_available_user").each(function(){
          if($(this).find(".contacts_name").text().toLowerCase().indexOf(text.toLowerCase()) >= 0) $(this).show();
          else $(this).hide();
        }); 
    });


    function handle(e, object) {
        var text = $(object).val();
        $(".contact_available_user").each(function(){
          if(text == "") $(this).hide();
          else if($(this).find(".contacts_name").text().toLowerCase().indexOf(text.toLowerCase()) >= 0 || $(this).find(".contacts_email").text().toLowerCase().indexOf(text.toLowerCase()) >= 0 ) $(this).show();
          else $(this).hide();
        }); 
    }

    function enterhandle(e, object) {
        
    }



    // var chatusers = [];
    function getUsers(email, callback) {
        $.ajax({
           url: site_url + 'chat/av_users',
           data: {
              email: email
           },
           success: function(data) {
              var jsonObj = JSON.parse(data);
              // if (email == '' && chatusers.length == 0) chatusers = jsonObj;
              console.log(jsonObj);
              callback(jsonObj);
           },
           type: 'POST'
        });
    }

    function buildUsersHTML(json, email, type, userIDs) {
        $(".modal-open ul#contacts").html("");
        if (json.length == 0 && email) {
            json.push({email:email, fname:"", lname:"", photo:"", uid:""});
        }
        json.forEach(function(item, index) {
            // console.log("#####################");
            // console.log(item);
            if(typeof userIDs !='undefined' && userIDs.toString().indexOf(item.id) > -1) return;
            var userName = item.fname+' '+item.lname;
            if (userName.trim() == '') {
                var nameArr = item.email.split('@');
                userName = nameArr[0];
            }

            if (!item.id) item.id = '';
            var htmlTxt = '<li class="contact_available_user container-widget" id="user_'+item.id+'" style="display:none;">';
            if (item.photo) htmlTxt += '<div class="col-sm-2 col-xs-3"><img class="avatar avatar_small" src="'+item.photo+'"></div>';
            else htmlTxt += '<div class="col-sm-2 col-xs-3"><img class="avatar avatar_small" src="<?= asset_base_url()."/images/emp-sm.jpg"?>"></div>';                        
            htmlTxt = htmlTxt + '<div class="col-sm-9 col-xs-8">' +
                            '<div class="row"><span class="contacts_name">'+userName+'</span></div>'+
                            '<div class="row"><span class="contacts_email">'+item.email+'</span></div></div>'+
                            '<div class="col-xs-1"><div class="glyphicon check-box-15 pull-right text-primary" aria-hidden="true" onclick="clkSelect(this, '+type+',\''+userName+'\', \''+item.email+'\', \''+item.id+'\')" style="border:1px solid;"></div></div>'+
                            '</li>';
            // alert(htmlTxt);
            $("#contacts").append(htmlTxt);
        });
    }

    function clkSelect(obj, type, uname, email, uid) {
        if (type == 1) {//private chat
            $(".glyphicon-ok").removeClass("glyphicon-ok").addClass("check-box-15");
            $(obj).addClass("glyphicon-ok");
            $(obj).removeClass("check-box-15");
            var htmlTxt = '<li class="online_tags pull-left" data-email="'+email+'" data-uid="'+uid+'">'+uname+'</li>';
            $(".modal-open #selected").html(htmlTxt);
            p_users = name;
            p_ids = uid;
        } else {//group chat
            if ($(obj).hasClass('glyphicon-ok')) {
                $(obj).removeClass('glyphicon-ok');
                $(obj).addClass("check-box-15");
                $("#selected li").each(function(){
                    if ($(this).attr("data-uid") == uid) {
                        $(this).remove();
                    }
                });
                contact_remove(null, uname, uid);
            } else {
                $(obj).addClass('glyphicon-ok');
                $(obj).removeClass('check-box-15');
                var htmlTxt = '<li class="online_tags pull-left" data-email="'+email+'" data-uid="'+uid+'">'+uname+'<a class="close x" style="color:white;" onclick="contact_remove(this, \''+uname+'\','+uid+')">&times;</a></li>';
                $("#selected").append(htmlTxt);
                contact_add(uname, uid);
            }
        }
    }

    function contact_remove(obj, uname, uid){
        index = g_users.indexOf(uname);
        if(index > -1){
          g_ids.splice(index, 1);
          g_users.splice(index, 1);
        } 
        if(obj) $(obj).parent().remove();

        $(".contact_available_user").each(function(){
            if ($(this).prop("id") === "user_"+uid) {
                var check = $(this).find(".glyphicon");
                $(check).removeClass('glyphicon-ok');
                $(check).addClass("check-box-15");
            }
        });
    }

    function contact_add(uname, uid){
        g_ids.push(uid);
        g_users.push(uname);
    }

    function add_non_user(){
      var email = $("#add_email").val();
      if(!validateEmail(email)){
        alert(email + " is invalid email.");
        return;
      }
      contact_add(email, 1);//non-user's uid 1 !
      var htmlTxt = '<li class="online_tags pull-left" data-email="'+email+'" data-uid="1">'+email+'<a class="close x" style="color:white;" onclick="contact_remove(this, \''+email+'\', 1)">&times;</a></li>';
      $("#selected").append(htmlTxt);
      $("#add_email").val("");
    }

    function tagClose(obj) {
        var strTxt = $(obj).parent().text();
        $("#contacts li.selected").each(function(){
            if(strTxt.indexOf($(this).find(".contacts_name").text())==0) {
                $(this).removeClass("selected");
                $(obj).parent().remove();
            }
        })
    }

    function add_non_user_to_private(){
      var email = $("#add_email").val();
      if(!validateEmail(email)){
        alert(email + " is invalid email.");
        return;
      }
      $("#selected li").each(function(){
          $(this).removeClass("glyphicon-ok").addClass("check-box-15");
      });
      var htmlTxt = '<div class="online_tags pull-left" data-email="'+email+'" data-uid="1">'+email+'</div>';
      $(".modal-open #selected").html(htmlTxt);
      p_users = email;
      p_ids = 1;
    }



</script>
</div>

	 
</div>
</body>
</html>