<?php
namespace OXT\App;

use TieuCA\WPSettings\WPSettings;

class Settings {
    
    use CommonProperties;
    
    public function __construct() {
        $this->initialize_common_properties();
        add_action('init', [$this, 'register']);

        // Thêm hook để lắng nghe sự kiện lưu cài đặt từ framework.
        add_action('wps_settings_saved', [$this, 'handle_license_activation'], 10, 2);
    }

    public function register() {
        
        $framework_config = [
            'version' => $this->version,
            'is_pro'  => self::isPro(),
        ];

        $settings = new WPSettings(__($this->name, 'optimize-tweaks'), $this->slug, $framework_config);
        
        // App cấu hình các thông số cho trang cài đặt của mình
        $settings->set_option_name($this->option_name);
        $settings->set_menu_position(5);
        $settings->set_capability('manage_options');
        $settings->set_menu_icon('dashicons-xing');
        $settings->set_version($this->version);
        $settings->set_icon( OXT_URL . 'src/Framework/resources/image/x.svg' );

        $add_days = (new \DateTime())->diff(new \DateTime('2025-02-14'))->days;
        $notice_message = sprintf(
            __( 'The plugin developer has dedicated <strong>%1$s</strong> days to this project. If you like it, you can support the author with <a href="%2$s" target="_blank">a beer 🍻 / coffee ☕️</a> !', 'optimize-tweaks' ),
            $add_days,
            '#'
        );
        $plugin_message = "<p>" . esc_html__('Like this plugin? Check out our other WordPress products:', 'optimize-tweaks') . "</p><a target='_blank' href='".esc_url('https://redxmedia.net/')."'>REDX.Media</a> - ".esc_html__('Create new elements for Flatsome', 'optimize-tweaks');

        $sidebar_items = [
            __('Write a review for ' .$this->name, 'optimize-tweaks').'<div class="handle-actions hide-if-no-js">📝</div>' => $notice_message,
            __('Our WordPress Products', 'optimize-tweaks').'<div class="handle-actions hide-if-no-js">⭐</div>' => $plugin_message,
        ];
        if (!self::isPro()) {
            $upgrade_message = "<p>" . esc_html__('Please upgrade to the PRO plan to unlock more awesome features.', 'optimize-tweaks') . "</p><a class='components-button is-secondary' target='_blank' href='https://redxmedia.net/lien-he/'>" . esc_html__('Get REDX EXtra PRO now', 'optimize-tweaks') . "</a>";
            $sidebar_items[__('Upgrade to Optimize X Tweaks PRO', 'optimize-tweaks').'<div class="handle-actions hide-if-no-js">🚀</div>'] = $upgrade_message;
        }
        $settings->set_sidebar($sidebar_items);
        
        $tab = $settings->add_tab('<span class="dashicons dashicons-plugins-checked"></span>'.__('Modules'));
        
        $section = $tab->add_section('<span class="dashicons dashicons-plugins-checked"></span>'.__('Modules'), ['description' => __('The module operates independently. Please enable it as needed.', 'optimize-tweaks')]);
        $section->add_option('module', [
            'name' => 'modules',
            'type'  => 'toggle',
            'label' => __('List Module', 'optimize-tweaks'),
            'options' => [
                'dashboard'	=> [
                    'label' 		=> __('Dashboard'),
                    'description' 	=> __('Please enable it as needed.'),
                    'icon' 			=> '<span class="dashicons dashicons-dashboard"></span>',
                ],
				'posts'	=> [
                    'label' 		=> __('Posts'),
                    'description' 	=> __('Chào mừng bạn đến với trang quản trị WordPress của bạn!'),
                    'icon' 			=> '<span class="dashicons dashicons-text"></span>',
                ],
				'comments'	=> [
                    'label' 		=> __('Comments'),
                    'description' 	=> __('Chào mừng bạn đến với trang quản trị WordPress của bạn!'),
                    'icon' 			=> '<span class="dashicons dashicons-text"></span>',
                ],
				'tools'	=> [
                    'label' 		=> __('Tools'),
                    'description' 	=> __(''),
                    'icon' 			=> '<span class="dashicons dashicons-superhero"></span>',
				],
				'control'	=> [
                    'label' 		=> __('Control'),
                    'description' 	=> __(''),
                    'icon' 			=>'<span class="dashicons dashicons-xing"></span>',
				],
            ],
        ]);
        
        if (self::get_option('modules') && in_array('dashboard',  self::get_option('modules'))) {
            
            
// Tạo một section
$tab = $settings->add_tab('<span class="dashicons dashicons-text"></span>'.__('API Integration', 'az-settings'));
$section = $tab->add_section(__('Editor toolbar'),
    ['description' => __('Please select your email provider and enter the required credentials.', 'az-settings')]);

// 1. Tạo trường điều khiển (công tắc)
//    Chúng ta cần đặt một ID rõ ràng cho nó.
$section->add_option('checkbox', [
    'id'      => 'enable_api_toggle', // ID để các trường khác theo dõi
    'name'    => 'enable_api',
    'label'   => __('Enable Custom API', 'az-settings'),
    'default' => false,
]);

// 2. Tạo trường phụ thuộc
$section->add_option('text', [
    'name'    => 'custom_api_keys',
    'label'   => __('Your API Key', 'az-settings'),
    'required' => true,
    'condition' => [
        'visible' => [
            [
                'id'       => 'enable_api_toggle', // Theo dõi ID của công tắc
                'operator' => 'is_checked',      // Điều kiện là "được chọn"
            ],
        ],
    ],
]);

// 1. Tạo trường điều khiển (ô nhập text)
$section->add_option('text', [
    'id'      => 'delete_confirmation_text', // ID để theo dõi
    'name'    => 'delete_text',
    'label'   => __('Type DELETE to proceed', 'az-settings'),
    'description' => __('Please type the exact word "DELETE" to confirm.'),
]);

// 2. Tạo trường phụ thuộc
$section->add_option('checkbox', [
    'name'    => 'confirm_delete',
    'label'   => __('Yes, I want to delete everything', 'az-settings'),
    'condition' => [
        'visible' => [
            [
                'id'       => 'delete_confirmation_text',
                'operator' => '==',
                'value'    => 'DELETE', // Điều kiện là giá trị phải BẰNG "DELETE"
            ],
        ],
    ],
]);       

// 1. Trường điều khiển (nhóm checkbox)
$section->add_option('checkbox-multiple', [
    'id'      => 'promotion_channels_multicheck', // ID để theo dõi
    'name'    => 'promotion_channels',
    'label'   => __('Select Promotion Channels', 'az-settings'),
    'options' => [
        'email'   => 'Email Marketing',
        'social'  => 'Social Media',
        'seo'     => 'SEO',
        'ads'     => 'Paid Ads',
    ],
]);

// 2. Trường phụ thuộc
$section->add_option('text', [
    'name'    => 'promotion_budget',
    'label'   => __('Promotion Budget ($)', 'az-settings'),
    'type'    => 'number',
    'min'    => '10',
    'max'    => '100',
    'condition' => [
        'visible' => [
            // Logic AND được áp dụng mặc định cho các quy tắc sau:
            
            // Quy tắc 1: Phải chứa 'email'
            ['id' => 'promotion_channels_multicheck', 'operator' => 'contains', 'value' => 'email'],

            // Quy tắc 2: Phải chứa 'social'
            ['id' => 'promotion_channels_multicheck', 'operator' => 'contains', 'value' => 'social'],
        ],
    ],
]);

$section->add_option('select', [
    'id'      => 'cancellation_reason_select',
    'name'    => 'cancellation_reason',
    'label'   => __('Reason for Cancellation', 'az-settings'),
    'options' => [ '' => '-- Select --', 'too_expensive' => 'Too Expensive', 'other' => 'Other' ]
]);

$section->add_option('textarea', [
    'name'    => 'cancellation_reason_other',
    'label'   => __('Other Reason', 'az-settings'),
    'condition' => [
        // Áp dụng cho chính nó, không cần 'target'
        'visible' => [
            ['id' => 'cancellation_reason_select', 'operator' => '==', 'value' => 'other'],
        ],
        'required' => [
            ['id' => 'cancellation_reason_select', 'operator' => '==', 'value' => 'other'],
        ],
    ]
]);



// 1. Trường sẽ bị vô hiệu hóa
$section->add_option('text', [
    'id'      => 'default_key_field', // Đặt ID cho nó
    'name'    => 'default_key',
    'label'   => __('Default API Key', 'az-settings'),
    'default' => 'DK-XXXXXXXX',
]);

// 2. Trường điều khiển
$section->add_option('checkbox', [
    'id'      => 'enable_custom_api_toggle',
    'name'    => 'enable_custom_api',
    'label'   => __('Use a Custom API Key', 'az-settings'),
]);

// 3. Một trường bất kỳ chứa logic điều kiện
$section->add_option('text', [
    'name'    => 'custom_api_key',
    'label'   => __('Your Custom API Key', 'az-settings'),
    'condition' => [
        'visible' => [
            ['id' => 'enable_custom_api_toggle', 'operator' => 'is_checked'],
        ],
        'disabled' => [
            'target' => 'default_key_field',
            'rules'  => [
                 ['id' => 'enable_custom_api_toggle', 'operator' => 'is_checked'],
            ]
        ]
    ]
]);

          
        }
        
        if (self::get_option('modules') && in_array('posts',  self::get_option('modules'))) {
            $tab = $settings->add_tab('<span class="dashicons dashicons-text"></span>'.__('Posts'));
            $section = $tab->add_section(__('Editor toolbar'));
            $section->add_option('checkbox', [
                'name' => 'mce_classic',
                'label' => __('Classic Editor'),
                'description' => __('Use the classic WordPress editor.', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox-multiple', [
                'name' => 'mce_plugin',
                'select' => true,
                'label' => __('TinyMCE Plugins'),
                'options' => [
                    'justify'	=> '<span class="dashicons dashicons-editor-justify"></span> '.__('Justify'),
    				'unlink'	=> '<span class="dashicons dashicons-editor-unlink"></span> '.__( 'Unlinks' ),
    				'letterspacing'	=> '<strong>[VA]</strong> '.__( 'Letter Spacing' ),
    				'changecase'	=> '<strong>[Aa]</strong> '.__('Change Case'),
    				'table'	=> '<span class="dashicons dashicons-editor-table"></span> '.__('Table'),
    				'visualblocks'	=> '<span class="dashicons dashicons-editor-paragraph"></span> '.__('Visual Blocks'),
    				'searchreplace'	=> '<span class="dashicons dashicons-code-standards"></span> '.__('Search Replace'),
    				'nofollow'	=> '<span class="dashicons dashicons-admin-links"></span> '.__('Add rel=nofollow & sponsored', 'optimize-tweaks'),
    				'cleanhtml'	=> '<span class="dashicons dashicons-editor-spellcheck"></span> '.__('Clean HTML', 'optimize-tweaks').' <pro>PRO</pro>',
    				'toc'	=> '<span class="dashicons dashicons-list-view"></span> '.__('Table of Contents').' <pro>PRO</pro>',
                ]
            ]);
            $section->add_option('checkbox', [
                'name' => 'signature',
                'label' => __('Signature', 'optimize-tweaks'),
                'description' => sprintf(__('Used %1$s or %2$s','optimize-tweaks'),
                    '<code>[signature]</code>',
                    '<span class="dashicons dashicons-clipboard"></span>'
                )
            ]);
            $section->add_option('wp-editor', [
                'name' => 'signature_content',
                'teeny' => true,
                'css' => ['hide_class' => 'signature hidden'],
                'label' => __('Content')
            ]);
            $section->add_option('choices', [
                'name' => 'signature_pos',
                'options' => [
                    '' => __( 'No' ),
                    'top'	=> __( 'Top' ),
    				'bottom'	=> __( 'Bottom' )
                ],
                'label' => __('Display Options').' '.__('Single Post'),
                'css' => ['hide_class' => 'signature hidden']
            ]);
            if ( wp_get_theme()->template !== 'flatsome' ) {
                $section->add_option('checkbox', [
                    'name' => 'classic_widget',
                    'label' => __('Classic Widgets','optimize-tweaks'),
                    'description' => __('Display a legacy widget.', 'optimize-tweaks')
                ]);
            }
            if ( !class_exists( 'RankMath' ) ) {
                $section->add_option('checkbox', [
                    'name' => 'mce_category',
                    'label' => __('Category Description', 'optimize-tweaks'),
                    'description' => __('Adds a tinymce editor to the category description box', 'optimize-tweaks')
                ]);
            }
            $section->add_option('checkbox-multiple', [
                'name' => 'mce_excerpt',
                'options' => fn() => array_combine(
                    $ids = array_filter(
                        array_diff(get_post_types(['public' => true]), ['attachment', 'revision', 'page', 'product']),
                        fn($id) => post_type_supports($id, 'excerpt')
                    ),
                    array_map(fn($id) => get_post_type_object($id)->label . " <code>$id</code>", $ids)
                ),
                'label' => __('Excerpt'),
                'description' => __('Add tinymce editor to the excerpt', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox-multiple', [
                'name' => 'publish_btn',
                'options'     => fn() => array_combine(
                    $ids = array_diff(get_post_types(['public' => true]), ['attachment', 'revision', 'blocks']),
                    array_map(fn($id) => get_post_type_object($id)->label . " <code>$id</code>", $ids)
                ),
                'label' => __('Publish Button', 'optimize-tweaks'),
                'description' => __('Making it stick to the bottom of the page when scrolling down the page', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox', [
                'name' => 'scrolltotop',
                'label' => __('Scroll To Top'),
                'default' => 1,
                'description' => __('Back To Top In WP Admin Area', 'optimize-tweaks')
            ]);
            
            $section = $tab->add_section(__('Posts Page'));
            $section->add_option('checkbox', [
                'name' => 'delete_attached',
                'label' => __('Delete Attached Media', 'optimize-tweaks')
            ]);
            $section->add_option('image', [
                'name' => 'media_default',
                'label' => __('Default featured image', 'optimize-tweaks'),
                'description' => __('This featured image will show up if no featured image is set', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox-multiple', [
                'name' => 'post_revisions',
                'options'     => fn() => array_combine(
                    $ids = array_diff(get_post_types(['public' => true]), []),
                    array_map(fn($id) => get_post_type_object($id)->label . " <code>$id</code>", $ids)
                ),
                'label' => __('Disable Post Revision'),
                'description' => __('Required to be true, as revisions do not support trashing.')
            ]);
            $section->add_option('checkbox', [
                'name' => 'img_column',
                'css' => ['hide_class' => 'pro'],
                'label' => __('Show Images'),
                'description' => __('Posts list')
            ]);
            $section->add_option('checkbox', [
                'name' => 'to_home',
                'label' => __('Redirect 404 to Home', 'optimize-tweaks'),
                'description' => __('Use the shortcode: <code>[redirect]</code>. If you are using the Flatsome Theme, it will be configured automatically.', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox', [
                'name' => 'redirect_single_post',
                'label' => __('Redirect Single Post', 'optimize-tweaks'),
                'description' => __('Redirect to the post if the search results return only one post.', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox', [
                'name' => 'tag_links',
                'css' => ['hide_class' => 'pro'],
                'label' => __('Tag links', 'optimize-tweaks'),
                'description' => __('Remove link in the tags from all post', 'optimize-tweaks')
            ]);
        }
    
        if (self::get_option('modules') && in_array('comments',  self::get_option('modules'))) {
// Giả sử bạn đang ở bên trong hàm register() của lớp Settings.php

// Bước 1: Tạo Tab chính cho Module
$tab_marketing = $settings->add_tab(
    '<span class="dashicons dashicons-email-alt"></span>' . __('Email Marketing', 'az-settings')
);

// ---

// Bước 2: Tạo Section điều khiển chính (luôn hiển thị)
$section_control = $tab_marketing->add_section(
    __('General Settings', 'az-settings')
);

// Thêm tùy chọn "Master Switch" để bật/tắt toàn bộ module.
// Chúng ta sẽ đặt một ID rõ ràng cho nó để các section/trường khác có thể theo dõi.
$section_control->add_option('checkbox', [
    'id'      => 'enable_email_marketing_module', // ID để các điều kiện khác gọi đến
    'name'    => 'enable_email_marketing',
    'label'   => __('Enable Email Marketing Features', 'az-settings'),
    'default' => false,
    'description' => __('Check this to activate all email marketing options below.', 'az-settings')
]);

// ---

// Bước 3: Tạo Section phụ thuộc (chỉ hiện khi "Master Switch" được bật)
$section_service_config = $tab_marketing->add_section(
    __('Service Configuration', 'az-settings'),
    [
        'description' => __('Please select your email provider and enter the required credentials.', 'az-settings'),
        
        // Áp dụng điều kiện cho cả section này
        'condition' => [
            'visible' => [
                ['id' => 'enable_email_marketing_module', 'operator' => 'is_checked'],
            ]
        ]
    ]
);

// Thêm trường "Chọn nhà cung cấp" vào section phụ thuộc.
$section_service_config->add_option('select', [
    'id'      => 'email_provider_select', // ID để các trường API Key theo dõi
    'name'    => 'email_provider',
    'label'   => __('Email Provider', 'az-settings'),
    'options' => [
        ''          => __('-- Select a provider --', 'az-settings'),
        'mailchimp' => 'Mailchimp',
        'sendgrid'  => 'SendGrid',
    ]
]);

// Thêm trường "Mailchimp API Key", chỉ hiển thị khi chọn Mailchimp
$section_service_config->add_option('text', [
    'id'      => 'mailchimp_api_key_field',
    'name'    => 'mailchimp_api_key',
    'label'   => 'Mailchimp API Key',
    'condition' => [
        'visible' => [
            // Logic AND: Phải thỏa mãn cả 2 điều kiện
            ['id' => 'enable_email_marketing_module', 'operator' => 'is_checked'],
            ['id' => 'email_provider_select', 'operator' => '==', 'value' => 'mailchimp'],
        ]
    ]
]);

// Thêm trường "Mailchimp List ID", cũng chỉ hiển thị khi chọn Mailchimp
$section_service_config->add_option('text', [
    'id'      => 'mailchimp_list_id_field',
    'name'    => 'mailchimp_list_id',
    'label'   => 'Mailchimp Audience ID',
    'condition' => [
        'visible' => [
            ['id' => 'email_provider_select', 'operator' => '==', 'value' => 'mailchimp'],
        ]
    ]
]);

// Thêm trường "SendGrid API Key", chỉ hiển thị khi chọn SendGrid
$section_service_config->add_option('text', [
    'id'      => 'sendgrid_api_key_field',
    'name'    => 'sendgrid_api_key',
    'label'   => 'SendGrid API Key',
    'condition' => [
        'visible' => [
            ['id' => 'email_provider_select', 'operator' => '==', 'value' => 'sendgrid'],
        ]
    ]
]);

        }
    
        if (self::get_option('modules') && in_array('tools',  self::get_option('modules'))) {
            $tab = $settings->add_tab('<span class="dashicons dashicons-superhero"></span>'.__('Tools'), 'tools');
            $section = $tab->add_section(__('Tools'), ['slug' => true, 'description' => __('You can transfer the saved options data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Import".', 'optimize-tweaks')]);
            $section->add_option('export_database', [
                'name' => 'wpdb_download',
                'view'  => 'export_database',
                'label' => __('Export to SQL', 'optimize-tweaks'),
                'description' => __('Administrator role user can take dump of the mysql database by single click.', 'optimize-tweaks')
            ]);
            
            $section->add_option('export', [
                'name'  => 'json_download',
                'view'  => 'export_json',
                'label' => __('Export Plugin Settings', 'optimize-tweaks'),
                'description' => __('Download current plugin configuration as a .json file.', 'optimize-tweaks'),
            ]);
            
            $section->add_option('import', [
                'name'  => 'json_upload',
                'view'  => 'import_json',
                'label' => __('Import Plugin Settings', 'optimize-tweaks'),
                'description' => __('Choose a .json file to restore your plugin settings.', 'optimize-tweaks'),
            ]);

        }
        
        if (self::get_option('modules') && in_array('control',  self::get_option('modules'))) {
            $tab = $settings->add_tab('<span class="dashicons dashicons-xing"></span>'.__('OXT'));
            $section = $tab->add_section(__('Control'));
            $section->add_option('select2', [
                'name' => 'no_backend',
                'multiple' => true,
                'options'     => fn() => array_combine(
                    $ids = array_diff(array_keys(get_editable_roles()), ['administrator']),
                    array_map(fn($id) => get_editable_roles()[$id]['name'], $ids)
                ),
                'label' => __('Restricted backend access for non-admins.', 'optimize-tweaks')
            ]);
            $section->add_option('checkbox-multiple', [
                'is_pro' => true,
                'name' => 'plugin_blocked_users',
                'options' => wp_list_pluck(get_users(['role' => 'administrator']), 'display_name', 'ID'),
                'label' => __('Blocked Admin Users', 'optimize-tweaks'),
                'description' => __('These users will be denied access to the plugin.')
            ]);
            $section->add_option('checkbox', [
                'name' => 'themeplugin_update',
                'label' => __('Theme & Plugin Auto-update', 'optimize-tweaks'),
                'description' => __('Allow')
            ]);
            $section->add_option('checkbox', [
                'name' => 'themeplugin_edits',
                'label' => __('Theme & Plugin Editors', 'optimize-tweaks'),
                'description' => __('Do not allow')
            ]);
            $section->add_option('checkbox', [
                'name' => 'core_updates',
                'label' => __('All Core Updates', 'optimize-tweaks'),
                'description' => __('Do not allow')
            ]);
            $section->add_option('checkbox', [
                'name' => 'debug_mode',
                'is_pro' => true,
                'label' => 'Debugging',
                'description' => __('Enable <code>WP_DEBUG</code> mode. <br /><strong>Warning:</strong> Please DISABLE this feature after use.', 'optimize-tweaks')
            ]);
            
            $section->add_option('checkbox', [
                'name' => 'misc_client_nags',
                'is_pro' => true,
                'label' => __('Nags & Notices', 'optimize-tweaks'),
                'description' => __('Hide')
            ]);
        }
        
        // Tab Bản quyền
        $license_tab = $settings->add_tab('<span class="dashicons dashicons-privacy"></span>'. __('License', 'optimize-tweaks'))
                                ->show_submit_buttons(false);

        $license_section = $license_tab->add_section(__('Activate Plugin', 'optimize-tweaks'));
        
        // SỬ DỤNG 'html' TYPE ĐỂ TẠO GIAO DIỆN TÙY CHỈNH
        $license_section->add_option('html', [
            'name' => 'license_key_ui', // Tên mới để không bị xung đột
            'render' => function() {
                $is_active = License::is_active();
                $masked_key = License::get_masked_key();
                ?>
                <style>
                    .license-control-wrapper {
                        display: flex;
                        align-items: center;
                        max-width: 500px;
                    }
                    .license-control-wrapper input[type="text"] {
                        flex-grow: 1;
                        border-right: none;
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                        font-family: monospace;
                        height: 32px;
                    }
                    .license-control-wrapper input[type="text"]:disabled {
                        background: #f0f0f0;
                        cursor: not-allowed;
                    }
                    .license-control-wrapper .components-button {
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;
                        white-space: nowrap;
                        box-shadow: none !important;
                        height: 32px;
                    }
                    .license-control-wrapper .spinner {
                        display: none;
                        margin: 0 5px 0 0;
                    }
                    .license-control-wrapper .is-busy {
                        cursor: wait;
                        display: inline-block;
                    }
                </style>
                <tr valign="top">
                    <th scope="row">
                        <label for="license-key-input"><?php _e('License Key', 'optimize-tweaks'); ?></label>
                    </th>
                    <td>
                        <div class="license-control-wrapper">
                            <input 
                                type="text" 
                                id="license-key-input" 
                                class="regular-text" 
                                placeholder="<?php echo $is_active ? esc_attr($masked_key) : 'Nhập license key của bạn...'; ?>"
                                value="<?php echo $is_active ? esc_attr($masked_key) : ''; ?>"
                                <?php echo $is_active ? 'disabled' : ''; ?>
                            >
                            
                            <!-- Nút Activate -->
                            <button 
                                type="button" 
                                id="activate-license-btn" 
                                class="components-button is-primary"
                                style="<?php echo $is_active ? 'display:none;' : ''; ?>"
                            >
                                <span class="spinner"></span>
                                <?php _e('Activate', 'optimize-tweaks'); ?>
                            </button>

                            <!-- Nút Deactivate -->
                            <button 
                                type="button" 
                                id="deactivate-license-btn" 
                                class="components-button is-destructive"
                                style="<?php echo !$is_active ? 'display:none;' : ''; ?>"
                            >
                                <span class="spinner"></span>
                                <?php _e('Deactivate', 'optimize-tweaks'); ?>
                            </button>
                        </div>
                         <p class="description">
                            <?php _e('Enter your license key to receive automatic updates and support.', 'optimize-tweaks'); ?>
                        </p>
                    </td>
                </tr>
                <?php
            }
        ]);
        
        // HIỂN THỊ TRẠNG THÁI
        $license_section->add_option('html', [
            'name' => 'license_status_display',
            'render' => function() {
                $status = License::is_active();
                $status_text = $status ? __('Active', 'optimize-tweaks') : __('Inactive', 'optimize-tweaks');
                $status_class = $status ? 'status-active' : 'status-inactive';
                echo '<tr valign="top" id="license-status-row"><th scope="row">' . __('Status', 'optimize-tweaks') . '</th><td><span class="license-status ' . $status_class . '">' . $status_text . '</span></td></tr>' .
                     '<style>.license-status{font-weight:bold;padding:4px 8px;border-radius:4px;color:#fff;}.status-active{background-color:#28a745;}.status-inactive{background-color:#dc3545;}</style>';
            }
        ]);
        
        $settings->make(); 
        
    }
    
    /**
     * Lấy giá trị một option cụ thể từ database.
     */
    public static function get_option($key, $fallback = null, $option_name = null) {
        if (empty($option_name)) {
            $option_name = Config::get('option_name');
        }
        $options = get_option($option_name);

        // Hàm get_option() có thể trả về `false`.
        if (!is_array($options)) {
            $options = [];
        }
        
        // Bây giờ, việc sử dụng array_key_exists sẽ an toàn.
        return array_key_exists($key, $options) && !empty($options[$key]) ? $options[$key] : $fallback;
    }
    
    /**
     * Kiểm tra một tính năng có được kích hoạt hay không.
     */
    public static function is_feature_active($key) {
        if (empty($option_name)) {
            $option_name = Config::get('option_name');
        }
        $options = get_option($option_name);

        // [SỬA LỖI] Kiểm tra an toàn tương tự như hàm get_option
        if (!is_array($options)) {
            return false;
        }
        
        return !empty($options[$key]) && $options[$key] !== '0';
    }
    
    /**
     * Xử lý kích hoạt license khi người dùng lưu cài đặt.
     * Hàm này được gọi bởi cả phương thức lưu truyền thống và AJAX.
     *
     * @param string $option_name Tên của option group được lưu.
     * @param array  $submitted_data Dữ liệu người dùng đã gửi lên từ form.
     */
    public function handle_license_activation($option_name, $submitted_data) {
        if ($option_name !== $this->option_name || !isset($submitted_data['license_key'])) {
            return;
        }
        License::activate(sanitize_text_field($submitted_data['license_key']));
    }
    
    /**
     * Phương thức trung tâm để kiểm tra trạng thái PRO.
     *
     * @return bool
     */
    public static function isPro() {
        return License::is_active();
    }
    
    /**
     * Trả về một class CSS dựa trên trạng thái PRO.
     */
    public static function isProClass() {
        return self::isPro() ? 'active' : 'inactive';
    }
}