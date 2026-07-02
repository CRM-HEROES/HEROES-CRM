<?php

namespace App\Utils\Field;

class Field
{
    public static function fields() {
        return [

            // Prospect

            ['slug' => 'title',               'name' => trans('prospect.fields.title'),               'for' => "prospect"],
            ['slug' => 'last_name',           'name' => trans('prospect.fields.last_name'),           'for' => "prospect"],
            ['slug' => 'first_name',          'name' => trans('prospect.fields.first_name'),          'for' => "prospect"],
            ['slug' => 'phone_number',        'name' => trans('prospect.fields.phone_number'),        'for' => "prospect"],
            ['slug' => 'mobile_phone_number', 'name' => trans('prospect.fields.mobile_phone_number'), 'for' => "prospect"],
            ['slug' => 'email',               'name' => trans('prospect.fields.email'),               'for' => "prospect"],
            ['slug' => 'company_name',        'name' => trans('prospect.fields.company_name'),        'for' => "prospect"],
            ['slug' => 'job_title',           'name' => trans('prospect.fields.job_title'),           'for' => "prospect"],
            ['slug' => 'website_url',         'name' => trans('prospect.fields.website_url'),         'for' => "prospect"],
            ['slug' => 'fax_number',          'name' => trans('prospect.fields.fax_number'),          'for' => "prospect"],
            ['slug' => 'street',              'name' => trans('prospect.fields.street'),              'for' => "prospect"],
            ['slug' => 'street_bis',          'name' => trans('prospect.fields.street_bis'),          'for' => "prospect"],
            ['slug' => 'state',               'name' => trans('prospect.fields.state'),               'for' => "prospect"],
            ['slug' => 'county',              'name' => trans('prospect.fields.county'),              'for' => "prospect"],
            ['slug' => 'postal_code',         'name' => trans('prospect.fields.postal_code'),         'for' => "prospect"],
            ['slug' => 'city',                'name' => trans('prospect.fields.city'),                'for' => "prospect"],
            ['slug' => 'country',             'name' => trans('prospect.fields.country'),             'for' => "prospect"],
            ['slug' => 'latitude',            'name' => trans('prospect.fields.latitude'),            'for' => "prospect"],
            ['slug' => 'longitude',           'name' => trans('prospect.fields.longitude'),           'for' => "prospect"],
            ['slug' => 'ip_address',          'name' => trans('prospect.fields.ip_address'),          'for' => "prospect"],
            ['slug' => 'date_of_birth',       'name' => trans('prospect.fields.date_of_birth'),       'for' => "prospect"],
            ['slug' => 'created_at',          'name' => trans('prospect.fields.created_at'),          'for' => "prospect", "editable" => false],
            ['slug' => 'updated_at',          'name' => trans('prospect.fields.updated_at'),          'for' => "prospect", "editable" => false],

            // User
            
            ['slug' => 'name',                'name' => trans('user.fields.name'),                'for' => "user"],
            ['slug' => 'last_name',           'name' => trans('user.fields.last_name'),           'for' => "user"],
            ['slug' => 'phone_number',        'name' => trans('user.fields.phone_number'),        'for' => "user"],
            ['slug' => 'mobile_phone_number', 'name' => trans('user.fields.mobile_phone_number'), 'for' => "user"],
            ['slug' => 'email',               'name' => trans('user.fields.email'),               'for' => "user"],
            ['slug' => 'street',              'name' => trans('user.fields.street'),              'for' => "user"],
            ['slug' => 'street_bis',          'name' => trans('user.fields.street_bis'),          'for' => "user"],
            ['slug' => 'city',                'name' => trans('user.fields.city'),                'for' => "user"],
            ['slug' => 'postal_code',         'name' => trans('user.fields.postal_code'),         'for' => "user"],
            ['slug' => 'country',             'name' => trans('user.fields.country'),             'for' => "user"],
            ['slug' => 'last_activity',       'name' => trans('user.fields.last_activity'),       'for' => "user"],
            ['slug' => 'password',            'name' => trans('user.fields.password'),            'for' => "user"],

            // Project

            ['slug' => 'capital',             'name' => trans('project.fields.capital'),             'for' => "project"],
            ['slug' => 'email',               'name' => trans('project.fields.email'),               'for' => "project"],
            ['slug' => 'naf',                 'name' => trans('project.fields.naf'),                 'for' => "project"],
            ['slug' => 'name',                'name' => trans('project.fields.name'),                'for' => "project"],
            ['slug' => 'phone_number',        'name' => trans('project.fields.phone_number'),        'for' => "project"],
            ['slug' => 'mobile_phone_number', 'name' => trans('project.fields.mobile_phone_number'), 'for' => "project"],
            ['slug' => 'postal_code',         'name' => trans('project.fields.postal_code'),         'for' => "project"],
            ['slug' => 'siret',               'name' => trans('project.fields.siret'),               'for' => "project"],
            ['slug' => 'street',              'name' => trans('project.fields.street'),              'for' => "project"],
            ['slug' => 'street_bis',          'name' => trans('project.fields.street_bis'),          'for' => "project"],
            ['slug' => 'city',                'name' => trans('project.fields.city'),                'for' => "project"],
            ['slug' => 'country',             'name' => trans('project.fields.country'),             'for' => "project"],

            // Product
            
            ['slug' => 'name', 'name' => "Nom", 'for' => "product"],
            ['slug' => 'description', 'name' => "Description", 'for' => "product"],
            ['slug' => 'currency', 'name' => "Devise", 'for' => "product"],
            ['slug' => 'price', 'name' => "Prix", 'for' => "product"],
            ['slug' => 'tax', 'name' => "Taxe", 'for' => "product"],
            ['slug' => 'including_tax', 'name' => "TTC", 'for' => "product"],
            
            ['slug' => 'name', 'name' => "Nom", 'for' => "order"],
            ['slug' => 'created_at', 'name' => "Date", 'for' => "order"],
            ['slug' => 'description', 'name' => "Description", 'for' => "order"],
            ['slug' => 'tax_value', 'name' => "Taxe", 'for' => "order"],
            ['slug' => 'total_including_tax', 'name' => "Prix avec taxe", 'for' => "order"],
            ['slug' => 'total_excluding_tax', 'name' => "Prix sans taxe", 'for' => "order"],
            ['slug' => 'currency', 'name' => "Devise", 'for' => "order"],
            
        ];
    }
}