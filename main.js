window.onload = function() {

  var bucket = "tmpdqneo";
  document.getElementById('files').addEventListener('change', function (event){
    setProgress(0, 'Upload started.');
    var files = event.target.files;
    var output = [];

    for (var i = 0; i < files.length; i++) {
      var file = files[i];
      var key = "1202/" + file.name;
      var contentType = file.type;
      getSignedUrl(bucket, key, contentType,
                   function(signedURL){// on success
                     uploadToS3(file, signedURL);
                   },
                   function(status) {// on error
                     setProgress(0, 'Could not contact signing script. Status = ' + status);
                   });
    }
  }
  , false);

  setProgress(0, 'Waiting for upload.');
};



/**
 * get Signed URL and Execute the callback
 */
function getSignedUrl(bucketName, objectKey, contentType, onSuccess, onError)
{
  var url = 'sign.php?bucket=' + bucketName + '&key=' + objectKey + '&type=' + contentType;
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);

  // Hack to pass bytes through unprocessed.
  xhr.overrideMimeType('text/plain; charset=x-user-defined');

  xhr.onreadystatechange = function(e) {
    if (this.readyState == 4 && this.status == 200)    {
      onSuccess(decodeURIComponent(this.responseText));
    }
    else if(this.readyState == 4 && this.status != 200)
    {
      onError(this.status);
    }
  };

  xhr.send();
}

function newCORSXHR(method, url) {
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
function uploadToS3(file, url)
{
  var xhr = newCORSXHR('PUT', url);
  if (!xhr) {
    setProgress(0, 'CORS not supported');
  } else {
    xhr.onload = function() {
      if(xhr.status == 200){
        setProgress(100, 'Upload completed.');
      } else {
        setProgress(0, 'Upload error: ' + xhr.status);
      }
    };

    xhr.onerror = function()     {
      setProgress(0, 'XHR error.');
    };

    xhr.upload.onprogress = function(e)     {
      if (e.lengthComputable)       {
        var percentLoaded = Math.round((e.loaded / e.total) * 100);
        setProgress(percentLoaded, percentLoaded == 100 ? 'Finalizing.' : 'Uploading.');
      }
    };

    xhr.setRequestHeader('Content-Type', file.type);
    xhr.setRequestHeader('x-amz-acl', 'public-read');

    xhr.send(file);
  }
}

function setProgress(percent, statusLabel)
{
  document.getElementById('pbar').value = percent;
  document.getElementById('pbar').max = 100;

  document.getElementById('progress_bar').className = 'loading';
  document.getElementById('status').innerText = statusLabel;
}
