<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

interface ManagementInterface
{
    /**
     * Create new item.
     *
     * @api
     * @param string $data.
     * @return string.
     */
    public function create(string $data);

    /**
     * Update item by id.
     *
     * @api
     * @param int $id.
     * @param string $data.
     * @return string.
     */
    public function update(int $id, string $data);

    /**
     * Remove item by id.
     *
     * @api
     * @param int $id.
     * @return bool.
     */
    public function delete(int $id);

    /**
     * Get item by id.
     *
     * @api
     * @param int $id.
     * @return bool.
     */
    public function get(int $id);

    /**
     * Get item by id and store id, only if item published
     *
     * @api
     * @param int $id
     * @param  int $storeId
     * @return bool.
     */
    public function view(int $id, int $storeId);

    /**
     * Retrieve list by page type, term, store, etc
     *
     * @param  string $type
     * @param  string $term
     * @param  int $storeId
     * @param  int $page
     * @param  int $limit
     * @return string
     */
    public function getList(string $type, string $term, int $storeId, int $page, int $limit);
}
