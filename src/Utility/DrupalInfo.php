<?php

namespace App\Utility;

/**
 * Provides static mappings for data from the drupal api.
 */
class DrupalInfo
{
    /**
     * Mapping of Drupal issue priorities
     *
     * @var array
     */
    protected static $priority = [
        '400' => 'Critical',
        '300' => 'Major',
        '200' => 'Normal',
        '100' => 'Minor',
    ];

    /**
     * Mapping of Drupal issue priority icons
     *
     * @var array
     */
    protected static $priorityIcon = [
        '400' => ':priority_critical:',
        '300' => ':priority_major:',
        '200' => ':priority_normal:',
        '100' => ':priority_minor:',
    ];

    /**
     * Mapping of Drupal issue statuses
     *
     * @var array
     */
    protected static $status = [
        '1' => 'Active',
        '2' => 'Fixed',
        '3' => 'Closed (duplicate)',
        '4' => 'Postponed',
        '5' => 'Closed (won\'t fix)',
        '6' => 'Closed (works as designed)',
        '7' => 'Closed (fixed)',
        '8' => 'Needs review',
        '13' => 'Needs work',
        '14' => 'Reviewed & tested by the community',
        '15' => 'Patch (to be ported)',
        '16' => 'Postponed (maintainer needs more info)',
        '17' => 'Closed (outdated)',
        '18' => 'Closed (cannot reproduce)',
    ];

    /**
     * Mapping of Drupal issue status icons.
     *
     * @var array
     */
    protected static $statusIcon = [
        '1' => ':status_active:',
        '2' => ':status_fixed:',
        '3' => ':status_duplicate:',
        '4' => ':status_duplicate:',
        '5' => ':status_duplicate:',
        '6' => ':status_duplicate:',
        '7' => ':status_closed:',
        '8' => ':status_needs_review:',
        '13' => ':status_needs_work:',
        '14' => ':status_rtbc:',
        '15' => ':status_rtbc:',
        '16' => ':status_duplicate:',
        '17' => ':status_duplicate:',
        '18' => ':status_closed:',
    ];

    /**
     * Mapping of Drupal issue categories
     *
     * @var array
     */
    protected static $category = [
        '1' => 'Bug Report',
        '2' => 'Task',
        '3' => 'Feature request',
        '4' => 'Support Request',
        '5' => 'Plan',
    ];

    /**
     * Provides the priorty string.
     *
     * @param $code
     * @return mixed|string
     */
    public static function getPriority($code): string {
        return static::$priority[$code] ?? 'Priority Unknown';
    }

    /**
     * Provides the priority icon.
     *
     * @param $code
     * @return string
     */
    public static function getPriorityIcon($code): string {
        return static::$priorityIcon[$code] ?? ':no_entry:';
    }

    /**
     * Provides the status string.
     *
     * @param $code
     * @return string
     */
    public static function getStatus($code): string {
        return static::$status[$code] ?? 'Status Unknown';
    }

    /**
     * Provides the status icon.
     *
     * @param $code
     * @return string
     */
    public static function getStatusIcon($code): string {
        return static::$statusIcon[$code] ?? ':no_entry:';
    }

    /**
     * Provides the category.
     *
     * @param $code
     * @return mixed|string
     */
    public static function getCategory($code) {
        return static::$category[$code] ?? 'Category Unknown';
    }

}
