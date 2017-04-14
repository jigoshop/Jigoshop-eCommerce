<?php
/**
 * api configuration file
 * contains which fields should be required when calling api creating functions
 * structure:
 * $className (name of class that is called or name that would be manually injected in class to validate)
 * => [?methodName => [field1, field2, ...]]
 * methodName could be omitted, then field requirements would be same for any validated method.
 * Empty array means that there are no fields required for this method.
 */
return [
    'attributes' => [
        'label',
        'type',
    ],
    'attributeOptions' => [
        'label',
    ],
    'customers' => [
        'post' => [
            'user_login',
            'user_pass',
            'user_email',
        ]
    ],

];