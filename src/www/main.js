var Uploader = {};

Uploader.onupload = function (event){
  Uploader.onProgress(0, 'Upload started.');
  var bucket = "tmpdqneo";
  var files = event.target.files;
  var output = [];
  var acl = 'public-read';

  for (var i = 0; i < files.length; i++) {
    var file = files[i];
    var key = "1202/" + file.name;
    var contentType = file.type;
    var meta = {myname: "DQNEO"};
    var url = 'sign.php?bucket=' + bucket + '&key=' + key + '&type=' + contentType + '&acl=' + acl + '&myname=' + meta.myname;
    Uploader.ajax(url,
                  function(responseJson){// on success
                    Uploader.uploadToS3(file, decodeURIComponent(responseJson.url), acl, meta);
                  },
                  function(status) {// on error
                    Uploader.onProgress(0, 'Could not contact signing script. Status = ' + status);
                  });
  }
};

/**
 * get Signed URL and Execute the callback
 */
Uploader.ajax = function(url, onSuccess, onError)
{
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);

  // Hack to pass bytes through unprocessed.
  xhr.overrideMimeType('text/plain; charset=x-user-defined');

  xhr.onreadystatechange = function(e) {
    if (this.readyState == 4 && this.status == 200)    {
      var json =JSON.parse(this.responseText);
      onSuccess(json);
    }
    else if(this.readyState == 4 && this.status != 200)
    {
      onError(this.status);
    }
  };

  xhr.send();
}

Uploader.newCORSXHR = function (method, url) {
  var xhr = new XMLHttpRequest();
  if ("withCredentials" in xhr) {
    xhr.open(method, url, true);
  } else if (typeof XDomainRequest != "undefined") {
    xhr = new XDomainRequest();
    xhr.open(method, url);
  } else {
    xhr = null;
  }
  return xhr;
}

/**
 * Use a CORS call to upload the given file to S3. Assumes the url
 * parameter has been signed and is accessable for upload.
 */
Uploader.uploadToS3 = function(file, url, acl, metadata)
{
  var xhr = this.newCORSXHR('PUT', url);
  if (!xhr) {
    return false;
  }

  xhr.onload = function() {
    if(xhr.status == 200){
      Uploader.onProgress(100, 'Upload completed.');
    } else {
      Uploader.onProgress(0, 'Upload error: ' + xhr.status);
    }
  };

  xhr.onerror = function()     {
    this.onProgress(0, 'XHR error.');
  };

  xhr.upload.onprogress = function(e)     {
    if (e.lengthComputable)       {
      var percentLoaded = Math.round((e.loaded / e.total) * 100);
      Uploader.onProgress(percentLoaded, percentLoaded == 100 ? 'Finalizing.' : 'Uploading.');
    }
  };

  xhr.setRequestHeader('Content-Type', file.type);
  xhr.setRequestHeader('x-amz-acl', acl);

  if (metadata) {
    for(var k in metadata) {
      xhr.setRequestHeader('x-amz-meta-' + k, metadata[k]);
    }
  }

  xhr.send(file);
}

