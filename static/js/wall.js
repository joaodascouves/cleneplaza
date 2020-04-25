function rpc_bind(item, formdata)
{
  if( item.tagName.toLowerCase() == 'input' )
  if( item.type.toLowerCase() != 'file' )
    formdata.append(item.name, item.value);
  else
    formdata.append(item.name, item.files[0]);
}

function rpc_send(action, data, callback)
{
  var request = new XMLHttpRequest();
  request.open('POST', action, true);
  request.send(data);

  request.onload = function()
  {
    if( request.status == 200 )
      callback(request.responseText);
  }

  return false;
}

function rpc_callback(data)
{
  var parsed_data = JSON.parse(data);
  if( parsed_data.status )
  {
    var center_modal = document.getElementById('center-modal');
    if( center_modal )
    {
      var span = ( document.getElementById('modal-span') ?
        document.getElementById('modal-span') : document.createElement('span') );

      span.id = 'modal-span';
      span.innerHTML = parsed_data.message;

      center_modal.appendChild(span);
      center_modal.style.display = 'inherit';
    }

    else
      console.log(data);
  }

  if( document.getElementById('send_file') )
    document.getElementById('send_file').disabled = true;

  if( document.getElementById('wall_image_preview') )
    document.getElementById('wall_image_preview').src = '/clenexyz/foto.jpg';
}

function wall_image_insert(image)
{
  var image_container = document.createElement('div');
  image_container.className = 'wall-image-container';

  var image_display = document.createElement('img');
  image_display.className = 'wall-image-file';
  image_display.src = image.uploadname;

  var post_link = document.createElement('a');
  post_link.href = "?context=post&postId=" + image.ID + '&action=show';
  post_link.innerHTML = "#" + image.ID;

  var profile_link = document.createElement('a');
  profile_link.href = '?context=profile&profileId=' + image.user_id;
  profile_link.innerHTML = ' ~'+ image.creator;

  var image_id = document.createElement('div');
  image_id.classList.add('wall-image-label', 'wall-image-label-top-left');
  image_id.appendChild(post_link);
  image_id.appendChild(profile_link);

  var post_stats = document.createElement('div');
  post_stats.classList.add('wall-image-label', 'wall-image-label-top-right');
  post_stats.innerHTML = '(W:' + image.words_count + '|R:0|I:0)';

  var image_filename = document.createElement('div');
  image_filename.classList.add('wall-image-label', 'wall-image-label-filename');
  image_filename.innerHTML = image.filename;

  image_container.appendChild(image_id);
  image_container.appendChild(image_display);
  image_container.appendChild(image_filename);
  image_container.appendChild(post_stats);

  return image_container;
}

function wall_image_get_count()
{
  return ( current_images ?
    current_images.length : 0 );
}

function wall_image_get_last_id()
{
  return ( current_images && current_images.length < 2 ?
    0 : parseInt(current_images[1].innerHTML.split('#')[1]) );
}

function wall_image_get_first_id()
{
  var last_hidden = ( current_images[current_images.length - 1].style.display == 'none' ? 2 : 1 );

  return ( current_images && current_images.length < 2  ?
    0 : parseInt(current_images[current_images.length - last_hidden].innerHTML.split('#')[1]) );
}

var current_images =
document.getElementsByClassName('wall-image-container');

function wall_image_pagination_update()
{
  var first_id = wall_image_get_first_id();
  var last_id = wall_image_get_last_id();

  var pagination = document.getElementById('wall-pagination-container');
  var page_match = window.location.href.match(/&page=([0-9]+)/);

  var page_num = ( page_match ? parseInt(page_match[1]) : 0 );

  var prev_button, next_button;
  if( wall_image_get_count() > 48 )
  {
    next_button = document.getElementById('next_button');
    if( !next_button )
    {
      next_button = document.createElement('a');
      next_button.id = 'next_button';
      next_button.innerHTML = 'Next';
    }

    next_button.href = '?context=home&offset=' + first_id + '&direction=DESC&page=' + parseInt(page_num + 1);
  }

  if( window.location.href.match(/offset=([0-9]+)&direction=(ASC|DESC)/) )
  {
    prev_button = document.getElementById('prev_button');
    if( !prev_button )
    {
      prev_button = document.createElement('a');
      prev_button.id = 'prev_button';
      prev_button.innerHTML = 'Previous';
    }

    prev_button.href = '?context=home';

    if( page_num > 1 )
      prev_button.href += '&offset=' + last_id + '&direction=ASC&page=' + parseInt(page_num - 1);
  }

  if( prev_button ) pagination.appendChild(prev_button);
  if( next_button ) pagination.appendChild(next_button);
}

function wall_image_grid_update_callback(data)
{
  var parsed_data = JSON.parse(data);
  if( parsed_data.status != 0 )
    return rpc_callback(data);

  var wall = document.getElementById('wall-container');
  var images_json = parsed_data.images;
  if( images_json.length<1 )
    return;

  var collection = document.getElementsByClassName('wall-image-container');
  if( collection.length >= 48 )
    images_json.forEach(function(item, index)
    {
      if( collection.length>1 )
        collection[collection.length - 1].remove();
    });

  var images = [];
  images_json.forEach(function(item, index)
  {
    images.push( wall_image_insert(images_json[index]) );
  });

  images.reverse();
  for( var i=0; i<images.length; i++ )
    if( !wall.firstElementChild )
      wall.appendChild(images[i]);

    else
    {
      if( wall.firstElementChild.id == 'upload_box' )
        wall.insertBefore(images[i], wall.firstElementChild.nextSibling);
      else
        wall.insertBefore(images[i], wall.firstChild);
    }

  if( collection.length >= 48 && document.getElementById('upload_box').style.display != 'none' )
    collection[collection.length - 1].style.display = 'none';

  wall_image_pagination_update();
}

function wall_image_grid_update(offset, direction, limit)
{
  var formdata = new FormData();
  formdata.append('offset', offset);
  formdata.append('direction', direction);
  formdata.append('limit', limit);

  rpc_send('api.php?context=post&action=collection_get', formdata, function(result){
    wall_image_grid_update_callback(result);
  });
}

function wall_image_preview_refresh(event)
{
  var preview = document.getElementById('wall_image_preview');
  if( preview )
  {
    preview.src = URL.createObjectURL(event.target.files[0]);
    document.getElementById('send_file').disabled = false;
  }
}

(function(){
  /*
    Navigates recursively through document forms, binding the RPC action
    in each input. Necessary to async image upload.
  */
  var recursive_bind = function(item, formdata)
  {
    if( item.hasChildNodes() )
      Array.from(item.children).forEach(function(item, index){
        recursive_bind(item, formdata);
      });
    else
      rpc_bind(item, formdata);
  }

  Array.from(document.getElementsByTagName('form')).forEach(function(item, index){
    item.onsubmit = function(){
      var formdata = new FormData();
      Array.from(item.children).forEach(function(item, index){
        recursive_bind(item, formdata);
      });

      return rpc_send(item.action, formdata, rpc_callback);
    }
  });
  /*
    End of recursive binding.
  */
  document.getElementById('post_trigger').addEventListener('change', function(event)
  {
    var upload_box = document.getElementById('upload_box');
    var images_collection = document.getElementsByClassName('wall-image-container');
    var last_image = ( images_collection.length>48 ? images_collection[images_collection.length-1] : null );

    upload_box.style.display = ( event.target.checked ? 'inherit' : 'none' );

    if( last_image )
      last_image.style.display = ( event.target.checked ? 'none' : 'inherit' );

    wall_image_pagination_update();
  });

  var parsed_url = new URL(window.location.href);
  var query_match = parsed_url.search.match(/&offset=([0-9]+)&direction=(ASC|DESC)/);

  if( query_match )
  {
    var offset = query_match[1];
    var direction = query_match[2];
    wall_image_grid_update(offset, direction, 48);

    document.getElementById('sync_trigger').addEventListener('change', function(event){
      if( event.target.checked )
        location.href = '?context=home&sync=true';
    });
  }
  else
  {
    // Update wall at page load.
    wall_image_grid_update(0, 'ASC', 48);

    var sync_interval;
    var sync_trigger = document.getElementById('sync_trigger');
    var post_trigger = document.getElementById('post_trigger');

    sync_trigger.addEventListener('change', function(event)
    {
      if( event.target.checked )
      {
        sync_interval = setInterval(function()
        {
          var offset = wall_image_get_last_id();
          wall_image_grid_update(offset, 'ASC', 48);

        }, 3000);

        post_trigger.disabled = false;
      }
      else
      {
        clearInterval(sync_interval);

        if( post_trigger.checked || post_trigger.disabled == false )
        {
          post_trigger.checked = false;
          post_trigger.disabled = true;

          var post_trigger_event = document.createEvent("HTMLEvents");
          post_trigger_event.initEvent('change', true, false, {checked: false});
          post_trigger.dispatchEvent(post_trigger_event);
        }
      }
    });

    if( window.location.href.match(/&sync=true/) )
    {
      sync_trigger.checked = true;
      var sync_trigger_event = document.createEvent("HTMLEvents");
      sync_trigger_event.initEvent('change', true, false, {checked: false});
      sync_trigger.dispatchEvent(sync_trigger_event);
    }
  }

})();
