var Uploader = {};

Uploader.countSuccess = 0;
Uploader.countFailure = 0;

Uploader.makeKey = function (filename) {
  return this.config.prefix +  filename;
};

Uploader.uploadFiles = function (files) {
  for (var i = 0; i < files.length; i++) {
    var file = files[i];
    var key = this.makeKey(file.name);
    var contentType = file.type;
    var acl = this.config.acl;
    var meta = this.config.meta;
    var bucket = this.config.bucket;

    var endpoint = this.config.endpoint;

    var url = 'sign.php?bucket=' + bucket + '&key=' + key + '&type=' + contentType + '&acl=' + acl + '&httpverb=PUT';
    for (var prop in meta) {
      url += '&' + prop + '=' + meta[prop];
    }

    this.ajax(url,file,key,
                  function(responseJson,file,objectKey){// on success
                    var query = decodeURIComponent(responseJson.query);
                    var url = '//' +  endpoint + '/' + bucket + '/' + objectKey + '?' + query;
                    //console.log(query);
                    //console.log(url);

                    Uploader.uploadToS3(file, url, acl, meta);
                  },
                  function(status,responseText) {// on error
                    Uploader.log('Could not contact signing script. Status = ' + status + 'Response=' + responseText);
                  });
  }
};

/**
 * get Signed URL and Execute the callback
 */
Uploader.ajax = function(url, file, key, onSuccess, onError)
{
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);

  // Hack to pass bytes through unprocessed.
  //xhr.overrideMimeType('text/plain; charset=x-user-defined');

  xhr.onreadystatechange = function(e) {
    if (this.readyState == 4 && this.status == 200)    {
      var json;
      try {
        json =JSON.parse(this.responseText);
      } catch(e) {
        onError(this.status, this.responseText);
        return;
      }
      onSuccess(json, file , key);
    }
    else if(this.readyState == 4 && this.status != 200)
    {
      onError(this.status, this.responseText);
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
      Uploader.onUploadSuccess(file);
    } else {
      Uploader.onUploadError(file, xhr);
    }
  };

  xhr.onerror = function() {
      Uploader.onUploadError(file, xhr);
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

Uploader.onUploadSuccess = function(file) {
  this.countSuccess++;
  this.log(this.countSuccess + ' files have been uploaded');
};

Uploader.onUploadError = function(file, xhr) {
  this.countFailure++;
  //override as you like
  this.log(file.name + " upload failed. status=" +  xhr.status);
};

Uploader.log = function(msg) {
  //override as you like
  console.log(msg);
  document.getElementById('status').innerText = msg;
};


