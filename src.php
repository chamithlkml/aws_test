<?php

require 'vendor/autoload.php';

$ec2Client = \Aws\Ec2\Ec2Client::factory(array(
    
    // User's amazon aws access key
    'key'    => 'AKIAIZ2SOT6CPAZH4V3A',
    
    // User's amazon aws access key secret
    'secret' => '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',
    
    // User's amazon aws instance region
    'region' => 'us-west-2',
));

$result = $ec2Client->getDescribeSecurityGroupsIterator(array(
        'DryRun' => false,
    
        //Security group we are considering
        'GroupNames' => array('database-servers') 
    ),array(
        'limit'     => 3,
        'page_size' => 10
    )
);

foreach($result as $key=>$iterate){
    
    //Check if any inbound rules are added
    if(isset($iterate['IpPermissions'])){
    
        //Record all inbound security rules
        $securityRules = $iterate['IpPermissions']; 
        
    }
}

//Empty array representing rules added only for 100.100.100.100/32
$rulesOnlyIn100 = array(); 


foreach($securityRules as $rule){
    
    //Empty array representing ip address range of the rules
    $ipRules = array();
    
    if(is_array($rule['IpRanges'])){
        
        foreach($rule['IpRanges'] as $ipRange){
            
            if(isset($ipRange['CidrIp'])){
                $ipRules[] = $ipRange['CidrIp'];
            }
        }
        
    }
    
    //Check for rules which are having only 100.100.100.100/32 ip range and not 200.200.200.200/32 range.
    if(in_array('100.100.100.100/32', $ipRules) && !in_array('200.200.200.200/32', $ipRules)){
        $rulesOnlyIn100[] = $rule;
    }
    
}

//Empty array representing new rules to be added
$newRules = array();

if(count($rulesOnlyIn100) > 0){
    
    foreach($rulesOnlyIn100 as $rule){
        $rule['IpRanges'] = array(
            array(
                //Change ip range into 200.200.200.200/32
                'CidrIp' => '200.200.200.200/32'
                )
        );
        
        $newRules[] = $rule;
        
    }
    
    //Add security
    $ec2Client->authorizeSecurityGroupIngress(array(
    'DryRun' => false,
    'GroupName' => 'database-servers',
    'IpPermissions' => $newRules
    
    ));
    
}