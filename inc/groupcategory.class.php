<?php

class PluginGroupcategoryGroupcategory extends CommonDBTM {

    /**
     * All categories
     * @var array
     */
    static private $_all_categories = [];

    /**
     * Get all categories
     *
     * @return array
     */
    public static function getAllCategories() {
        if (empty(PluginGroupcategoryGroupcategory::$_all_categories)) {
            $category = new ITILCategory();
            $categories = $category->find([],"completename ASC, level ASC, id ASC");

            self::$_all_categories = $categories;
        }

        return PluginGroupcategoryGroupcategory::$_all_categories;
    }

    /**
     * Get the selected categories for a group
     *
     * @param  Group $group
     * @return array
     */
    public static function getSelectedCategoriesForGroup(Entity $group) {
        $group_category = new PluginGroupcategoryGroupcategory();

        if ($group_category->getFromDBByCrit(["group_id" => $group->getId()])) {
            $category_ids = explode(', ', $group_category->fields['category_ids']);
            $all_categories = self::getAllCategories();
            $selected_categories = [];
            foreach ($all_categories as $details) {
                if (in_array($details['id'], $category_ids)) {
                    $selected_categories[$details['id']] = $details['completename'];
                }
            }
        } else {
            $selected_categories = [];
        }

        return $selected_categories;
    }

    /**
     * Get the categories for a user
     *
     * @param int $user_id
     * @return array
     */
    public static function getUserCategories($user_id) {
        $user_categories = [];

        $user = new User();

        if ($user->getFromDB($user_id)) {
            //$user_groups = Group_User::getUserGroups($user_id);
            $user_groups = Profile_User::getUserEntities($user_id, true);

            foreach ($user_groups as $tmp => $ID_entity) {
                $group = new Entity();
                if ($group->getFromDB($ID_entity)) {
                    $categories = self::getSelectedCategoriesForGroup($group);
                    $user_categories += $categories;
                }
            }
        }

        return $user_categories;
    }

    /**
     * Hook callback when an item is shown
     *
     * @param array $params
     */
    static function post_show_item($params) {

        if (!is_array($params['item'])) {
            switch ($params['item']->getType()) {
                case 'Entity':
                    plugin_groupcategory_post_show_group($params['item']);
                    break;

                case 'Ticket':
                    plugin_groupcategory_post_show_ticket($params['item']);
                    break;
                default:
                // nothing to do
            }
        } else {
            // here we are going to view a Solution
            return;
        }
    }
    
    static function post_item_form($params) {
        if (!is_array($params['item'])) {
            switch ($params['item']->getType()) {
                case 'Entity':
                    plugin_groupcategory_post_show_group($params['item']);
                    break;

                case 'Ticket':                    
                    plugin_groupcategory_post_show_ticket($params['item']);
                    break;
                default:
                    //plugin_groupcategory_post_show_group($params['item']);
                    //break;
                // nothing to do
            }
        } else {
            // here we are going to view a Solution
            return;
        }
    }

}
