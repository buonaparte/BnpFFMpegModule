BnpFFMpegModule
===============

This module provides a simple wrapper for the [PHP_FFmpeg](https://github.com/alchemy-fr/PHP-FFmpeg) library,
exposing the library as a ZendFramework service.

Configuration
-------------

Configure the module, by copying and adjusting `config/module.config.php.dist` to your config include path:

``` php
  $ffmpegConfig = array(
      'configuration' => array(
          'ffmpeg.threads'   => 4,
          'ffmpeg.timeout'   => 300,
          'ffmpeg.binaries'  => '/opt/local/ffmpeg/bin/ffmpeg',
      ),
      /**
       * Custom logger service, must resolve to a Psr\Logger\LoggerInterface instance pulled from the ServiceManager
       */
      'logger' => 'ffmpeg_logger',
      /**
       * Custom FFProbe service, pulled from the ServiceManager
       */
      'ffprobe' => 'ffprobe_service'
  );

  return array(
      /**
       * Root Module configuration
       */
      'bnp-ffmpeg-module' => array(
          /**
           * For single ffmpeg service instance you can just uncomment the bellow line
           */
  //        'ffmpeg' => $ffmpegConfig,
          /**
           * For multiple ffmpeg services with different configuration you will specify them in an array,
           * from to the service name to service configuration
           */
  //        'ffmpeg' => array(
  //            'FFMpeg1' => array_merge_recursive($ffmpegConfig, array()),
  //            'FFMpeg2' => array_merge_recursive($ffmpegConfig, array()),
  //        ),
          /**
           * FFProbe configuration
           */
          'ffprobe' => array(
              'configuration' => array(
                  'ffprobe.timeout'  => 30,
                  'ffprobe.binaries' => '/opt/local/ffmpeg/bin/ffprobe',
              ),
              /**
               * Custom logger service must resolve to a Psr\Logger\LoggerInterface instance pulled from the ServiceManager
               */
              'logger' => 'ffprobe_logger',
              /**
               * Custom cache service must resolve to a Doctrine\Common\Cache\Cache instance pulled from the ServiceManager
               */
              'cache' => 'ffprobe_cache'
          )
      ),

      /**
       * Service Manager config
       */
      'service_manager' => array(
  //        'factories' => array(
  //            /**
  //             * FFProbe service factory
  //             */
  //            'FFProbe' => 'BnpFFMpegModule\Factory\FFProbeServiceFactory',
  //            /**
  //             * For single ffmpeg service instance you can just register the factory for the service name
  //             */
  //            'FFMpeg' => 'BnpFFMpegModule\Factory\FFMpegServiceFactory'
  //        ),
  //        'abstract_factories' => array(
  //            /**
  //             * For multiple ffmpeg service instances you must register the FFMpeg abstract factory
  //             */
  //            'BnpFFMpegModule\Factory\FFMpegAbstractServiceFactory'
  //        )
      )
  );
```

Usage
-----

```php
	$ffmpeg = $this->get('FFMpeg');

	// Open video
	$video = $ffmpeg->open('/your/source/folder/input.avi');

	// Resize to 720x480
	$video
        ->filters()
        ->resize(new Dimension(720, 480), ResizeFilter::RESIZEMODE_INSET)
        ->synchronize();

    // Start transcoding and save video
    $video->save(new X264(), '/your/target/folder/video.mp4');
```
