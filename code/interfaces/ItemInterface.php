<?php

/**
 * Add this interface to models which are extended with the FeedMeItemModelExtension. This
 * is used to identify FeedMe extended models e.g. while syncing via FeedMeSyncTask. We can't
 * declare the extension method prototypes here as they are implemented in the extension not
 * on the extended model, though they are here for reference.
 */
interface FeedMeItemInterface {

    /**
     * Called by FeedMeItemModelExtension when it has finished importing the feed.
     * @return mixed
     */
    public function feedMeImported();

    /**
     * Called by FeedMeItemModelExtension when it has finished updating the item model.
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