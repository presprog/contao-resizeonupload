# Resize images on upload
Define image sizes per folder in the back end to have the thumbnails automatically generated after uploading them to your server. Additionally, there is a console command to generate all the missing thumbnails.

## Resize on upload
1. Create a new folder and select the image sizes that should be generated
2. Create as many sub folders with additional image sizes, if you wish.
3. Upload an image to a folder. The bundle will generate the image sizes defined for this folder and its parents.

## Resize with a console command
This bundle provides a console command for your convenience. Just define your image sizes in the back end and run the command as follows to generate every missing thumbnail:

```
$ vendor/bin/contao-console resizeonupload:generate-thumbs
```

## Why?
Contao usually creates thumbnails on-the-fly: Whenever you request to resize a given image, Contao creates a settings file per image first without actually resizing the image. When the user than loads the website in the browser, the browser requests that image and only then the image will actually be resized – or it won't when an image is never requested, making it a very efficient process.

However, this requires the client (the browser) to wait for the image to be generated. We once had the need to provide a mobile app with images in the correct size, that the app itself requested via a custom JSON API, but it did not wait for the response. The solution was to organize images by topic/usage and assign these folders the right image size, i.e. create a 150px×150px thumb for every image in the folder `avatars`. Whenever a new avatar was uploaded, we would generate the new thumbs ahead of time.

----

Made with ♥️ and ☕ by [Present Progressive](https://www.presentprogressive.de)
