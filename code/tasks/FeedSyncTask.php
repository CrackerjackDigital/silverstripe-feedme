<?php
/**
 * Task to update RssFeeds with new related PostModel's from the feed source.
 *
 *  Iterates through all feeds and calls importFeed method on them.
 */

class FeedMeSyncTask extends BuildTask {

    protected $title = 'FeedMe Module Feed Import Task';

    protected $description = 'Import all Feed Me feeds from their sources';

    // set this in config to admin email. If not set then an extend.feedMeAdminEmail will try and get an address.
    private static $administrator_email = '';

    // set in config to name of class which represents a feed (has 'FeedMeFeedModelExtension')
    private static $feedme_feed_model_class = '';

    // add the class names of extended feed classes which you do not want to be synced by this task here.
    // alternatively just remove the implementation of 'FeedMeFeedInterface' will also do it
    // but this option can be set at runtime via config.
    private static $excluded_feed_class_names = [];

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     */
    public function run($request) {
        if (!$adminEmail = $this->config()->get('administrator_email')) {
            $contenders = $this->extend('feedMeAdminEmail') ?: [];
            $adminEmail = reset($contenders);
        }
        if ($adminEmail) {
            SS_Log::add_writer(new SS_LogEmailWriter($adminEmail, SS_Log::INFO));
        }
        // anything like a warning or above
        SS_Log::add_writer(new SS_LogEmailWriter(Security::findAnAdministrator()->Email), SS_Log::WARN);

        $excludedFeedClasses = $this->config()->get('excluded_feed_class_names');

        // for each implementor of the FeedMeFeedInterface check if it's not excluded then for each
        // instance of that model call feedMeImport on it.

        $implementors = ClassInfo::implementorsOf('FeedMeFeedInterface');
        foreach ($implementors as $className) {
	        // chance to disable a feed by setting config.excluded_feed_class_names
            if (!in_array($className, $excludedFeedClasses)) {
                /** @var FeedMeFeedModelExtension $feedModel */

                foreach ($className::get() as $feedModel) {
                    $feedModel->feedMeImport();
                }
            }
        }
    }
}