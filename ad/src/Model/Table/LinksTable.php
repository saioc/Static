<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class LinksTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Users');
        $this->hasMany('Statistics');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('url', 'Please add a URL.')
            ->add('url', 'checkUrl', [
                'rule' => function ($value, $context) {
                    $url_parts = parse_url($value);

                    if ($url_parts['scheme'] == 'magnet') {
                        return true;
                    }

                    if (\Cake\Validation\Validation::url($value)) {
                        return true;
                    }

                    return false;
                },
                'last' => true,
                'message' => __('URL is invalid.')
            ])
            ->add('url', 'checkProtocol', [
                'rule' => function ($value, $context) {
                    $scheme = parse_url($value, PHP_URL_SCHEME);

                    if (in_array($scheme, ['http', 'https', 'magnet'])) {
                        return true;
                    }
                    return false;
                },
                'last' => true,
                'message' => __('http, https and magnet urls only allowed.')
            ])
            /*
            ->add('url', 'uniqueURL', [
                'rule' => function ($value, $context) {
                    $count = $this->find('all')
                        ->where([
                            'url' => $value,
                            'alias' => $context['data']['alias'],
                            'user_id' => $context['data']['user_id'],
                            'ad_type' => $context['data']['ad_type'],
                            'status' => 1
                        ])
                        ->count();


                    if( isset($context['data']['id']) && !empty($context['data']['id']) ) {
                        //$count->where(['id !=' => $context['data']['id']]);
                    }


                    if ($count > 0) {
                        return false;
                    }
                    return true;
                },
                'last' => true,
                'message' => __('This link is already existing.')
            ])
            */
            ->add('url', 'disallowedDomains', [
                'rule' => function ($value, $context) {
                    $disallowed_domains = explode(',', get_option('disallowed_domains'));
                    $disallowed_domains = array_map('trim', $disallowed_domains);
                    $disallowed_domains = array_filter($disallowed_domains);

                    $disallowed_domains = array_merge($disallowed_domains, array_values(get_all_domains_list()));
                    $url_main_domain = parse_url($value, PHP_URL_HOST);

                    if (in_array(strtolower($url_main_domain), $disallowed_domains)) {
                        return false;
                    }
                    return true;
                },
                'last' => true,
                'message' => __('This domain is not allowed on our system.')
            ])
            ->add('url', 'checkGoogleSafeUrl', [
                'rule' => function ($value, $context) {
                    $google_safe_browsing_key = get_option('google_safe_browsing_key');

                    if (empty($google_safe_browsing_key)) {
                        return true;
                    }

                    // https://developers.google.com/safe-browsing/v4/reference/rest/v4/ClientInfo

                    $url = "https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$google_safe_browsing_key}";
                    $method = 'POST';
                    $data = '{
                        "client": {
                          "clientId":      "yourcompanyname",
                          "clientVersion": "1.5.2"
                        },
                        "threatInfo": {
                          "threatTypes":      ["MALWARE", "SOCIAL_ENGINEERING", "POTENTIALLY_HARMFUL_APPLICATION", "UNWANTED_SOFTWARE", "MALICIOUS_BINARY"],
                          "platformTypes":    ["ANY_PLATFORM"],
                          "threatEntryTypes": ["URL"],
                          "threatEntries": [
                            {"url": "' . $value . '"},
                          ]
                        }
                      }';

                    $headers = ['Content-Type: application/json'];

                    $result = @json_decode(curlRequest($url, $method, $data, $headers), true);

                    if (isset($result['matches'])) {
                        return false;
                    }
                    return true;
                },
                'last' => true,
                'message' => __("Google currently report this URL as an active phishing, malware, or unwanted website.")
            ])
            ->add('url', 'checkPhishtankSafeUrl', [
                'rule' => function ($value, $context) {
                    $phishtank_key = get_option('phishtank_key');

                    if (empty($phishtank_key)) {
                        return true;
                    }

                    // https://www.phishtank.com/api_info.php

                    $url = 'http://checkurl.phishtank.com/checkurl/';
                    $method = 'POST';
                    $data = [
                        'url' => $value,
                        'format' => 'json',
                        'app_key' => $phishtank_key
                    ];

                    $result = @json_decode(curlRequest($url, $method, $data), true);

                    if (isset($result['results']['in_database']) && $result['results']['in_database'] === true) {
                        return false;
                    }

                    return true;
                },
                'last' => true,
                'message' => __("PhishTank currently report this URL as an active phishing website.")
            ])
            ->requirePresence('alias', 'create')
            ->notEmpty('alias', __('Please add an alias.'))
            ->add('alias', 'maxLength', [
                'rule' => ['maxLength', 30],
                'last' => true,
                'message' => __('Maximum alias length is 30 characters.')
            ])
            ->add('alias', 'alphaNumericDashUnderscore', [
                'rule' => function ($value, $context) {
                    return (bool)preg_match('|^[0-9a-zA-Z]*$|', $value);
                },
                'last' => true,
                'message' => __('Alias should be a alpha numeric value')
            ])
            ->add('alias', 'checkReserved', [
                'rule' => function ($value, $context) {
                    $reserved_aliases = explode(',', get_option('reserved_aliases'));
                    $reserved_aliases = array_map('trim', $reserved_aliases);
                    $reserved_aliases = array_filter($reserved_aliases);

                    if (in_array(strtolower($value), $reserved_aliases)) {
                        return false;
                    }
                    return true;
                },
                'last' => true,
                'message' => __('This alias is a reserved word.')
            ])
            ->add('alias', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'last' => true,
                'message' => __('Alias already exists.')
            ])
            ->add('ad_type', 'inList', [
                'rule' => ['inList', [0, 1, 2]],
                'last' => true,
                'message' => __('Choose a valid value.')
            ]);

        return $validator;
    }

    public function validationReport(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
            ->notEmpty('reason', __('Please select a reason to report.'))
            ->add('reason', 'inList', [
                'rule' => [
                    'inList',
                    ['404', 'dead_links', 'no_links', 'masked_links', 'premium_links', 'virus', 'other']
                ],
                'message' => __('Please enter a valid reason')
            ]);
    }

    public function isOwnedBy($alias, $user_id)
    {
        return $this->exists(['alias' => $alias, 'user_id' => $user_id]);
    }

    public function geturl()
    {
        do {
            $min = get_option('alias_min_length', 4);
            $max = get_option('alias_max_length', 8);

            $numAlpha = rand($min, $max);
            $out = $this->generateurl($numAlpha);
            while ($this->checkReservedAuto($out)) {
                $out = $this->generateurl($numAlpha);
            }
            $alias_count = $this->find('all')
                ->where(['alias' => $out])
                ->count();
        } while ($alias_count > 0);
        return $out;
    }

    //http://blog.justni.com/creating-a-short-url-service-using-php-and-mysql/
    public function generateurl($numAlpha)
    {
        $listAlpha = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $generateurl = '';
        $i = 0;
        while ($i < $numAlpha) {
            $random = mt_rand(0, strlen($listAlpha) - 1);
            $generateurl .= $listAlpha{$random};
            $i = $i + 1;
        }
        return $generateurl;
    }

    public function getLinkMeta($long_url)
    {
        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (parse_url($long_url, PHP_URL_SCHEME) == 'magnet') {
            return $linkMeta;
        }

        $headers = get_http_headers($long_url);

        if (isset($headers['content-type']) && stripos($headers['content-type'], 'text/html') === false) {
            return $linkMeta;
        }

        $content = curlHtmlHeadRequest($long_url);

        if (!empty($content)) {
            $doc = new \DOMDocument();
            // UTF-8 Encoding Fix
            // http://www.php.net/manual/en/domdocument.loadhtml.php#95251
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $content);
            $nodes = $doc->getElementsByTagName('title');


            if (!empty($nodes->item(0)->nodeValue)) {
                $title = $nodes->item(0)->nodeValue;
                $linkMeta['title'] = $this->cleanMeta($title);
            }

            $metas = $doc->getElementsByTagName('meta');

            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);

                if (empty($linkMeta['description']) && $meta->getAttribute('name') == 'description') {
                    $description = $meta->getAttribute('content');
                    $linkMeta['description'] = $this->cleanMeta($description);
                }

                if (empty($linkMeta['image']) && $meta->getAttribute('property') == 'og:image') {
                    $linkMeta['image'] = $meta->getAttribute('content');
                }
            }
        }

        return $linkMeta;
    }

    public function cleanMeta($meta)
    {
        return preg_replace("/\r|\n/", "", strip_tags($meta));
    }

    public function checkReservedAuto($keyword)
    {
        //$reserved_aliases = explode( ',', Configure::read( 'Option.reserved_aliases' ) );
        $reserved_aliases = [];
        if (in_array($keyword, $reserved_aliases)) {
            return true;
        }
        return false;
    }
}
