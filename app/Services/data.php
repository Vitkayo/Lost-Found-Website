<?php

namespace App\Services;

class Data
{
    public static $contacts_data = [
        1 => [ 'id'=>1,
            'name'=>'Sok Dara',
            'gender'=>'Male',
            'email'=>'sok.dara@rupp.edu.kh',
            'phone'=>'010 123 123',
            'company'=>'Company 1'],
        2 => [ 'id'=>2,
            'name'=>'Sok Pisey',
            'gender'=>'Female',
            'email'=>'sok.pisey@rupp.edu.kh',
            'phone'=>'011 123 123',
            'company'=>'Company 2'],
        3 => [ 'id'=>3,
            'name'=>'Sros Bopha',
            'gender'=>'Female',
            'email'=>'sros.bopha@rupp.edu.kh',
            'phone'=>'090 909 123',
            'company'=>'Company 2'],
        4 => [ 'id'=>4,
            'name'=>'Tith Sovan',
            'gender'=>'Male',
            'email'=>'tith.sovan@rupp.edu.kh',
            'phone'=>'090 902 283',
            'company'=>'Company 1'],
        5 => [ 'id'=>5,
            'name'=>'Chan Leakhena',
            'gender'=>'Female',
            'email'=>'chan.leakhena@rupp.edu.kh',
            'phone'=>'090 233 322',
            'company'=>'Company 2']
    ];

    public static $companies_data = [
        1 => ['name'=>'Company 1'],
        2 => ['name'=>'Company 2']
    ];
}
