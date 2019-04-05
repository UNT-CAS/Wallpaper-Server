This project generates the current background image in the resolution needed for the computer making the request. [What??](#description) [Why???](#usage)

# Description

This server takes assets and slaps them together to create a wallpaper image of the requested resolution. Because the wallpaper image is created on the fly, the wallpaper server can generate a wallpaper for any aspect ratio requested; even portrait (sideways) monitors.

There are three assets used:

- *Background*: The background image of the wallpaper. This is currently just a single pixel of the green color that we want.
- *TopLeft*: This is the an image we want in the top left; like the company logo.
- *BottomRight*: This is an image we want in the bottom right; like support contact information.

# Usage

The main issue presented iteself in OS X when the Lock Screen Wallpaper image had to *fit* the screen resolution because OS X wouldn't resize or stretch it as needed.

We also use it for Windows because we can only specify one file (via GPO) to force as the Lock Screen Wallpaper. Since logos stretch and stretched logos make VIPs sad ... we decided we needed to to be a little more creative with choosing the image for the Lock Screen Wallpaper. This server allows an unskewed image with the the exact resolution to be used for the Lock Screen Wallpaper.

## Getting *Your* Assets In

If you're going to use this, you're likely going to want a simple way of getting your assets loaded into the container. Well, there's an [environment variable for that](#environment-variables): `WALLPAPER_CURL_CONFIG`.

When the container launches, before Apache runs (via [run.sh](docker/apache/run.sh)) we pull in the assets defined in the [curl config](docker/apache/curl.config) file. During this process, all assets are purged from the [assets directory](html/assets) and your assets are downloaded.

For more detail about the format of the config file, [see the curl man page](https://curl.haxx.se/docs/manpage.html#-K). Keep in mind, the working directory during this is the [assets directory](html/assets), so there's no need to specify a file name or path. See our development [curl config](docker/apache/curl.config) file for some guidance.

## Query String Parameters

See for yourself with two quick examples (please feel free to play; [submit an issue when you break it](https://github.com/UNT-CAS-ITS/Wallpaper-Server/issues)):

- **4:3**: https://unt-wallpaper-dev.azurewebsites.net?w=800&h=600
- **16:9**: https://unt-wallpaper-dev.azurewebsites.net?w=848&h=480

By default, this will return a BMP; for windows.

However, OS X needs PNG with Alpha Channel:

- **4:3**: https://unt-wallpaper-dev.azurewebsites.net?w=800&h=600&f=png
- **16:9**: https://unt-wallpaper-dev.azurewebsites.net?w=848&h=480&f=png

**Note:** Only `bmp` and `png` are supported formats.

### More Query String Parameters

Here's a list and quick desciption of all of the current *Query String Parameters*:

| Parameter | Name | Default Value | Expected Value(s)/Type | Description | 
| --- | --- | --- | --- | --- |
| `bg` | Background | `bg` | Check the `/assets` folder for possible images; supply the case-sensitive name without extension. | This image will be stretch to the height and width needed to fill the background. |
| `bl` | Bottom Left | `NULL` | Check the `/assets` folder for possible images; supply the case-sensitive name without extension. | This image will be used in the appropriate corner. |
| `blp` | Bottom Left Percentage | `0` | Should be a number less than or equal to `1`. | This is the percentage of the width of the *Bottom Left* image. |
| `blm` | Bottom Left Margin Percentage | `0` | Should be a postive number less than or equal to `1`. Two numbers can be supplied, seperated by a comma (no space). i.e.: `.05` or `.05,.07` | This is the percentage of the width that will be used to apply a margin around the *Bottom Left* image. It works like [CSS's margin shorthand property](https://www.w3schools.com/css/css_margin.asp); however, only one or two values are supported. |
| `br` | Bottom Right | `NULL` | Check the `/assets` folder for possible images; supply the case-sensitive name without extension. | This image will be used in the appropriate corner. |
| `brp` | Bottom Right Percentage | `0` | Should be a number less than or equal to `1`. | This is the percentage of the width of the *Bottom Right* image. |
| `brm` | Bottom Right Margin Percentage | `0` | Should be a postive number less than or equal to `1`. Two numbers can be supplied, seperated by a comma (no space). i.e.: `.05` or `.05,.07` | This is the percentage of the width that will be used to apply a margin around the *Bottom Right* image. It works like [CSS's margin shorthand property](https://www.w3schools.com/css/css_margin.asp); however, only one or two values are supported. |
| `f` | Format | `bmp` | `bmp`, `png` | The format of the image. |
| `h` | Height | ***REQUIRED*** | *int* | The desired height of the image. |
| `tl` | Top Left | `est-1890-UNT-University-of-North-Texas-white` | Check the `/assets` folder for possible images; supply the case-sensitive name without extension. | This image will be used in the appropriate corner. |
| `tlp` | Top Left Percentage | `.6` | Should be a number less than or equal to `1`. | This is the percentage of the width of the *Top Left* image. |
| `tlm` | Top Left Margin Percentage | `0` | Should be a postive number less than or equal to `1`. Two numbers can be supplied, seperated by a comma (no space). i.e.: `.05` or `.05,.07` | This is the percentage of the width that will be used to apply a margin around the *Bottom Right* image. It works like [CSS's margin shorthand property](https://www.w3schools.com/css/css_margin.asp); however, only one or two values are supported. |
| `tr` | Top Right | `NULL` | Check the `/assets` folder for possible images; supply the case-sensitive name without extension. | This image will be used in the appropriate corner. |
| `trp` | Top Right Percentage | `0` | Should be a number less than or equal to `1`. | This is the percentage of the width of the *Top Right* image. |
| `trm` | Top Right Margin Percentage | `0` | Should be a postive number less than or equal to `1`. Two numbers can be supplied, seperated by a comma (no space). i.e.: `.05` or `.05,.07` | This is the percentage of the width that will be used to apply a margin around the *Bottom Right* image. It works like [CSS's margin shorthand property](https://www.w3schools.com/css/css_margin.asp); however, only one or two values are supported. |
| `w` | Width  | ***REQUIRED*** | *int* | The desired width of the image. |

## Cache

Cached requests are here; in case you're curious:

- https://unt-wallpaper-dev.azurewebsites.net/cache

***Note:*** *Cached files are `touch`ed everytime they are accessed.*
***Note:*** *Server time is in [GMT](http://www.lmgtfy.com/?q=What+is+GMT%3F).*

# Docker

This has been setup to run in Docker. Simply use git to clone this repo to the Docker server. Then, build and run the Docker image with `docker-compose`:

```bash
docker-compose up -d
```

Force a rebuild:

```bash
docker-compose build
```

Get a bash shell inside of the container (on the `php` service):

```bash
docker-compose exec php /bin/bash
```

## Production

Add it to the swarm:

```bash
docker stack deploy -c wallpaper-server.yml wallpaper-server
```

### Environment Variables

All of the [query string parameters](#more-query-string-parameters) are available as an environment variable. Here's a couple of quick examples of setting the `br`, `brm`, and `brp` parameters via environment variables:

```
# The rest of this code block is one line
docker run -it -p 8080:80 -e WALLPAPER_BR=other_image -e WALLPAPER_BRP=.3 -e WALLPAPER_BRM=0 --rm wallpaper
```

```yaml
environment:
  - WALLPAPER_BR=other_image
  - WALLPAPER_BRP=.3
  - WALLPAPER_BRM=0
```

Addtionally, there are a couple of additional environment variable available:

| Parameter | Name | Default Value | Expected Value(s)/Type | Description | 
| --- | --- | --- | --- | --- |
| `WALLPAPER_README` | ReadMe | `https://github.com/UNT-CAS-ITS/Wallpaper-Server` | A URL. | This is used to overwrite the error messages, in case you've forked this project and would prefer to point to it. |
| `WALLPAPER_CURL_CONFIG` | Curl Config | `https://raw.githubusercontent.com/UNT-CAS-ITS/Wallpaper-Server/master/docker/apache/curl.config` | A URL. | This is used to specify a custom curl config file. This is pretty much a required environment variable if you're going to use this for your own purposes. |
| `WALLPAPER_CURL_CONFIG_HEADER` | Curl Header | `NULL` | A Header, like: `PRIVATE-TOKEN:R2WwRii944lO6d7bvk8FDo` | If a private token is needed to authenticate to your URL, you can supply it here. |

**Note:** Query string parameters (aka: GET parameters) will overwrite environment variable settings.
