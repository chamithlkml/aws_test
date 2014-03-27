<?php

require 'vendor/autoload.php';

fwrite(STDOUT, "Initiating..." . PHP_EOL);

$ec2Client = \Aws\Ec2\Ec2Client::factory(array(
    
    // User's amazon aws access key
    'key'    => 'AKIAIZ2SOT6CPAZH4V3A',
    
    // User's amazon aws access key secret
    'secret' => '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',
    
    // User's amazon aws instance region
    'region' => 'us-west-2',
));

fwrite(STDOUT, "Reading security rules..." . PHP_EOL);

$result = $ec2Client->getDescribeSecurityGroupsIterator(array(
        'DryRun' => false,
    
        //Security group we are considering
        'GroupNames' => array('database-servers') 
    ),array(
        'limit'     => 3,
        'page_size' => 10
    )
);

if(!$result){
    
    fwrite(STDOUT, "Erroroooooooo" . PHP_EOL);
}

foreach($result as $key=>$iterate){
    
    //Check if any inbound rules are added
    if(isset($iterate['IpPermissions'])){
    
        //Record all inbound security rules
        $securityRules = $iterate['IpPermissions']; 
        
    }
}

//Empty array representing rules added only for 100.100.100.100/32
$rulesOnlyIn100 = array(); 

$RulesCount = count($securityRules);

if($RulesCount == 0){
    
    fwrite(STDOUT, "Found no security rules..." . PHP_EOL);
    exit();
    
}

fwrite(STDOUT, "Found $RulesCount types of security rule(s)..." . PHP_EOL);

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

$ip100RuleCount = count($rulesOnlyIn100);

if($ip100RuleCount == 0){
    fwrite(STDOUT, "No rules found added only for 100.100.100.100/25 ip range. Exiting..." . PHP_EOL);
    exit();
}


fwrite(STDOUT, "$ip100RuleCount rule(s) found only for 100.100.100.100/32 range and need to be added to 200.200.200.200/32 range" . PHP_EOL);

foreach($rulesOnlyIn100 as $rule){
    $rule['IpRanges'] = array(
            array(
                //Change ip range into 200.200.200.200/32
                'CidrIp' => '200.200.200.200/32'
                )
    );
        
    $newRules[] = $rule;
        
}
    
//Adding new security rules security
$response = $ec2Client->authorizeSecurityGroupIngress(array(
    
    'DryRun' => false,
    'GroupName' => 'database-servers',
    'IpPermissions' => $newRules
    
));

fwrite(STDOUT, "Successfully updated the security group." . PHP_EOL);