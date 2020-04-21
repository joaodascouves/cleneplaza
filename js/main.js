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

function wall_image_insert(id, uri, filename, creator)
{
  var image_container = document.createElement('div');
  image_container.className = 'wall-image-container';

  var image_display = document.createElement('img');
  image_display.className = 'wall-image-file';
  image_display.src = uri;

  var image_id = document.createElement('div');
  image_id.classList.add('wall-image-label', 'wall-image-label-id');
  image_id.innerHTML = '#' + id;

  if( filename.length > 30 )
    filename = filename.substr(0, 30) + '...';

  var image_filename = document.createElement('div');
  image_filename.classList.add('wall-image-label', 'wall-image-label-filename');
  image_filename.innerHTML = filename;

  var image_creator = document.createElement('div');
  image_creator.classList.add('wall-image-label', 'wall-image-label-creator')
  image_creator.innerHTML = '(' + creator + ')';

  image_container.appendChild(image_display);
  image_container.appendChild(image_id);
  image_container.appendChild(image_filename);
  image_container.appendChild(image_creator);

  return image_container;
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
  if( collection.length>1 )
    images_json.forEach(function(item, index)
    {
      if( collection.length>1 )
        collection[collection.length - 1].remove();
    });

  var images = [];
  images_json.forEach(function(item, index)
  {
      images.push( wall_image_insert(images_json[index].ID,
        images_json[index].uploadname,
        images_json[index].filename,
        images_json[index].creator) );
  });

  images.reverse();
  for( var i=0; i<images.length; i++ )
  {
    if( !wall.firstElementChild )
      wall.appendChild(images[i]);

    else
    {
      if( wall.firstElementChild.id == 'upload_box' )
        wall.insertBefore(images[i], wall.firstElementChild.nextSibling);
      else
        wall.insertBefore(images[i], wall.firstChild);
    }
  }
}

function wall_image_grid_update(limit, offset)
{
  var formdata = new FormData();
  formdata.append('limit', limit);
  formdata.append('offset', offset);

  rpc_send('api.php?action=wall_collection_get', formdata, function(result){
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

function wall_get_last_image_id()
{
  var current_images = document.getElementsByClassName('wall-image-container');
  if( current_images.length == 1 )
    return 0;

  return parseInt(current_images[1].innerHTML.split('#')[1]);
}

setInterval(function()
{
  var offset = wall_get_last_image_id();
  wall_image_grid_update(47, offset);

}, 3000);
