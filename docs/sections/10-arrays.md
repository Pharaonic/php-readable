## Arrays

`Pharaonic\Readable\Arr` answers three common questions about an array's shape.

### Only nulls?

`Arr::isNull()` is `true` when every value is `null`. The check is strict: `false`, `0` and `''` are not null.

```php title="example.php"
Arr::isNull([null, null]);  // true
Arr::isNull([null, 0]);     // false
Arr::isNull([false]);       // false
Arr::isNull([]);            // true
```

An empty array has no non-null value, so it returns `true`.

### Nested arrays?

`Arr::isMultidimensional()` is `true` when at least one value is an array, including an empty one:

```php title="example.php"
Arr::isMultidimensional([1, 2]);              // false
Arr::isMultidimensional([1, [2]]);            // true
Arr::isMultidimensional(['x' => ['y' => 1]]); // true
Arr::isMultidimensional(['a' => []]);         // true
Arr::isMultidimensional([]);                  // false
```

### A list?

`Arr::isList()` is `true` when the keys are `0, 1, 2, …` in order:

```php title="example.php"
Arr::isList([10, 20, 30]);           // true
Arr::isList([]);                     // true
Arr::isList([1 => 'a']);             // false
Arr::isList([1 => 'b', 0 => 'a']);   // false
Arr::isList(['a' => 1]);             // false
```

:::info Numeric string keys
PHP converts the key `'0'` to the integer `0` when it builds the array, so `Arr::isList(['0' => 'a'])` is `true`.
:::
