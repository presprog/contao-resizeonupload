# Resize images on upload
Define image sizes per folder in the back end to have the thumbnails automatically generated after uploading. If you are already in the middle of a project or purged the image cache, run the console command to have all missing thumbnails generated. 

# Resize on upload
1. Create a folder and define the image sizes to be generated
2. Optionally: Create as many subfolders with additional image sizes, if you wish. 
3. Upload an image to a folder. The bundle will generate the image sizes defined for this folder and its parents.

# Resize with a console command
This bundle provides a console command for your convenience. Just define your image sizes in the back end and run the command as follows to generate every missing thumbnail:

```
# Generate missing thumbnails for every folder that has defined image sizes
vendor/bin/contao-console presprog:thumbs:generate
```