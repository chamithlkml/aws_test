<?php

require 'vendor/autoload.php';


$ec2Client = \Aws\Ec2\Ec2Client::factory(array(
    'key'    => 'AKIAIZ2SOT6CPAZH4V3A',
    'secret' => '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',
    'region' => 'us-west-2',
));

$result = $ec2Client->getDescribeSecurityGroupsIterator(array(
        'DryRun' => false,
        'GroupNames' => array('database-servers')
    ),array(
        'limit'     => 3,
        'page_size' => 10
    )
);

foreach($result as $key=>$iterate){
    $securityRules = $iterate['IpPermissions'];
}

$rules_200 = array();

foreach($securityRules as $rule){
    
    if($rule['IpRanges'][0]['CidrIp'] == '100.100.100.100/32'){
        
        $rule['IpRanges'][0]['CidrIp'] = '200.200.200.200/32';
        $rules_200[] = $rule;
    }
}


$ec2Client->authorizeSecurityGroupIngress(array(
    'DryRun' => false,
    'GroupName' => 'database-servers',
    'IpPermissions' => $rules_200
    
));