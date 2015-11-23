<?php

/**
 * Add this interface to models which are extended with the FeedMeFeedModelExtension. This
 * is used to identify FeedMe extended models e.g. while syncing via FeedMeSyncTask. We can't
 * declare the extension method prototypes here as they are implemented in the extension not
 * on the extended model, though they are here for reference.
 */
interface FeedMeFeedModelInterface {

    /**
     * Called by FeedMeFeedModelExtension when it has finished importing the feed.
     *
     * @param array $valuesFromFeed - all values from feed, may not have changed though.
     * @return mixed
     */
    public function feedMeImported(array $valuesFromFeed = []);

    /**
     * Called by FeedMeFeedModelExtension when it has finished updating the feed model.
     *
     * @param $updatedFields - array of fields which were updated
     * @return mixed
     */
    public function feedMeUpdated(array $updatedFields = []);

    /**
     * Read the external feed and import it and all its items as related data objects.
     * @return mixed
     */
//    public function feedMeImport();

    /**
     * Update the Feed model itself, doesn't touch related items.
     *
     * @param array $feedMeNeutralMap
     * @param array $imported
     * @return mixed
     */
//    public function feedMeUpdate(array $feedMeNeutralMap, array &$imported = []);

}