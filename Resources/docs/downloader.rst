Download command
================
Download command allows to download files from remote location.
 
Working with download command
-----------------------------

Downloader should listen to pipeline download source event and return downloaded files.
 
Example:

.. code-block:: php

    /**
     * Dummy downloader for testing.
     */
    class DummyDownloader extends AbstractDownloader
    {
        /**
         * {@inheritdoc}
         */
        public function download($dir, $path)
        {
            // Do some actual downloading here.
            $filename = 'data.txt';
            file_put_contents("$dir/$path/$filename", 'content_foobar');
    
            $this->notify($path, 'file');
    
            return [$filename];
        }
    }
    
..

And yml file:

.. code-block:: yml

    project.downloader.dummy:
        class: %project.downloader.dummy.class%
        calls:
            - [ setDispatcher, [ @event_dispatcher ] ]
        tags:
            - name: kernel.event_listener
              event: ongr.pipeline.ongr_download.provider_foo.source
              method: onSource
..

Then command:

    ongr:remote:download provider_foo
    
will download data.txt with content "content_foobar"



