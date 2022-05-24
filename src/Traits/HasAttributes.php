<?php

declare(strict_types=1);

namespace Mralston\Bark\Traits;

use Carbon\Carbon;

trait HasAttributes
{
    protected array $dates = [];

    protected array $casts = [];

    protected array $transforms = [];

    protected array $hidden = [];

    public function fill($attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (!$this->fillable($key)) {
                continue;
            }

            if (
                isset($this->transforms[$key]) &&
                method_exists($this, $method = 'transform' . ucwords($this->transforms[$key]))
            ) {
                $this->$key = $this->$method($value);
                continue;
            }

            if (isset($this->casts[$key])) {
                $method = 'cast' . ucwords($this->casts[$key]);

                if (method_exists($this, $method)) {
                    $this->$key =  $this->$method($value);
                    continue;
                }

                $this->$key = new $this->casts[$key]($value);

                continue;
            }

            if (in_array($key, $this->dates)) {
                $this->$key = $this->castDate($value);
                continue;
            }

            $this->$key = $value;
        }

        return $this;
    }

    public function fillable(string $attribute)
    {
        return property_exists($this, $attribute);
    }

    public function attrs(): array
    {
        $attrs = [];

        foreach ($this as $key => $value) {
            if (!in_array($key, ['client', 'dates', 'casts', 'transforms', 'hidden', ...$this->hidden])) {
                $attrs[$key] = $value;
            }
        }

        return $attrs;
    }

    private function castArray($value): array
    {
        $output = [];

        foreach ($value as $k => $v) {
            $output[$k] = $v;
        }

        return $output;
    }

    private function castBool($value): bool
    {
        return boolval($value);
    }

    protected function castDate($date): Carbon
    {
        if (is_numeric($date)) {
            return Carbon::createFromTimestamp($date);
        }

        return Carbon::parse($date);
    }
}
