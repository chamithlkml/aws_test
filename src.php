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


$rulesOnlyIn100 = array();



foreach($securityRules as $rule){
    
    $ipRules = array();
    
    if(is_array($rule['IpRanges'])){
        
        foreach($rule['IpRanges'] as $ipRange){
            
            if(isset($ipRange['CidrIp'])){
                $ipRules[] = $ipRange['CidrIp'];
            }
        }
        
    }
    
    
    
    if(in_array('100.100.100.100/32', $ipRules) && !in_array('200.200.200.200/32', $ipRules)){
        $rulesOnlyIn100[] = $rule;
    }
    
}

$newRules = array();

if(count($rulesOnlyIn100) > 0){
    
    foreach($rulesOnlyIn100 as $rule){
        $rule['IpRanges'] = array(
            array( 
                'CidrIp' => '200.200.200.200/32'
                )
        );
        
        $newRules[] = $rule;
        
    }
    
    $ec2Client->authorizeSecurityGroupIngress(array(
    'DryRun' => false,
    'GroupName' => 'database-servers',
    'IpPermissions' => $newRules
    
    ));
    
}