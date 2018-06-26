# ABOS-PHP
A better object syntax, JSON and PHP Array

## Installation
### Composer
```sh
composer require smidswater/abos
```


## API
```js
\Smidswater\ABOS::decode(/* JSON HERE */); //eg. file.json or "{stringified json}" or array
```


## What does it solve?
With this module your json / array becomes self-conscious so you can use @top (Root of JSON), @parent (Parent of current scope) or @this (Current scope).

### Demo
```php
var_dump(ABOS::decode([
    'jsonItem1' => 'Hello',
    'jsonItem2' => [
        'jsonItem3' => '${@top.jsonItem1} W',
        'jsonItem4' => '${@this.jsonItem3}or'
    ],
    'jsonItem3' => [
        'jsonItem5' => '${@this.jsonItem4}',
        'jsonItem4' => '${@parent.jsonItem2.jsonItem4}ld'
    ]
]));
```

Resolves to

```json
{
    "jsonItem1": "Hello",
    "jsonItem2": {
        "jsonItem3": "Hello W",
        "jsonItem4": "Hello Wor",
    },
    "jsonItem3": {
        "jsonItem4": "Hello World"
    }
}
```
