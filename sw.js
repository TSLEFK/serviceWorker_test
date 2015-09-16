/* Service Workerのグローバルオブジェクトでは外部スクリプト読み込みの関数がデフォルトで存在する。 */
//importScripts("https://raw.githubusercontent.com/coonsta/cache-polyfill/master/index.js");
importScripts('serviceworker-cache-polyfill.js');

/**
 * Service Workerインストール時の関数
 * ==========
 * オフライン時、キャッシュに登録されていないURLにアクセスした場合に表示するHTMLを取得する。
 * オンライン時にこのファイルにアクセスすることがないため。
*/
 /*
addEventListener("install", function(event)
{
  event.waitUntil(
    caches.open("myCache")
      .then(function(cache)
      {
        console.log("cache add all complete");
        return cache.addAll(["/index.html"]);
      })
  );
});
*/
//公式からのソース
var CACHE_NAME = 'my-site-cache-v1';
var urlsToCache = [
  './'
];

self.addEventListener('install', function(event) {
 // インストール処理
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('cache add All');
        return cache.addAll(urlsToCache);
      })
  );
});


/**
 * スコープ内でファイル取得のリクエストが飛んだ時の関数
 * スコープはService Workerのjsファイル以下のディレクトリになる。
 * serviceWorker.register()時に明示的に指定することもできる。
 * ==========
 * 1. オンライン時は普通にファイルを取得させ、キャッシュにも保存させる。
 * 2. オフライン時はキャッシュされているファイルを返すようにする。
 * 3. リクエストしたURLに対応するファイルがキャッシュにない場合は、インストール時に登録したnodata.htmlを返す。
 */

addEventListener("fetch", function(event){
  var online = navigator.onLine;
  
  if(online){
    console.log("online get files and save cache");
    /* 1 */
    event.respondWith(
      fetch(event.request)
        .then(function(response)
        {
          if(!response || response.status != 200){
            console.log("in if not 200");
            return;
          }

          caches.open("myCache")
            .then(function(cache)
            {
              console.log("in myCache");
              cache.put(event.request, response);
            });
        })
    );
  }else{
    event.respondWith(
      caches.match(event.request)
        .then(function(response)
        {
          /* 2 */
          if(response)
          {
            console.log("Now offline. return cache");
            return response;
          }
          /* 3 */
          else if(event.request.context == "internal")
          {
            console.log("Now offline. return nodata");
            return caches.match("index.html")
              .then(function(responseNodata)
              {
                return responseNodata;
              });
          }
        })
    );
  }
});