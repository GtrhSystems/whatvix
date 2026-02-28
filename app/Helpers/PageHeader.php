<?php

namespace App\Helpers;

final class PageHeader
{
    public static ?self $instance = null;

    public ?string $title = null;
    public ?array $segments = null;
    public ?array $buttons = null;
    public ?array $overviews = null;

    /**
     * Constructs a new instance of the PageHeader class.
     *
     * @param string|null $title The title of the page header. If provided, it will be set as the header title.
     * @param array|null $buttons An array of buttons to add to the page header. Each button should be an array with parameters for the addButton method.
     * @param array|null $overviews An array of overviews to add to the page header. Each overview should be an array with parameters for the addOverview method.
     * @param array|null $segments An array of URL segments. If not provided, the current request's segments will be used.
     */

    public function __construct(
        ?string $title = null,
        ?array $buttons = null,
        ?array $overviews = null,
        ?array $segments = null,
    ) {

        if ($title) {
            $this->title = $title;
        }

        if ($buttons) {
            foreach ($buttons as $button) {
                $this->addButton(...$button);
            }
        }

        if ($overviews) {
            foreach ($overviews as $overview) {
                $this->addOverview(...$overview);
            }
        }

        if (!$segments) {
            $this->segments = request()->segments();
        }
    }

    /**
     * Set the page header instance with the specified attributes.
     *
     * This method initializes the page header with the provided title, segments, buttons, and overviews,
     * creating a new instance of the PageHeader class. If an instance already exists, it will be overwritten.
     *
     * @param string $title The title of the page header.
     * @param array|null $segments Optional URL segments for the page header. Defaults to null.
     * @param array|null $buttons Optional array of buttons for the page header. Each button should be an array with specific keys. Defaults to null.
     * @param array|null $overviews Optional array of overview items for the page header. Each item should be an array with specific keys. Defaults to null.
     * @return self Returns the instance of the PageHeader class.
     */

    public static function set(
        string $title = '',
        ?array $segments = null,
        ?array $buttons = null,
        ?array $overviews = null
    ): self {
        self::$instance = new self(
            title: $title,
            buttons: $buttons,
            overviews: $overviews,
            segments: $segments
        );
        return self::$instance;
    }

    /**
     * Retrieve a value from the page header array using a dot-notated key.
     *
     * @param string $key The dot-notated key to search for in the page header array.
     * @param mixed $default The default value to return if the key is not found. Defaults to null.
     * @return mixed The value associated with the given key, or the default value if the key is not found.
     */

    public static function get(string $key, $default = null)
    {
        return data_get(self::toArray(), $key, $default);
    }

    /**
     * Set the URL segments of the page header.
     *
     * @param array $segments An array of URL segments. If not provided, the current URL segments will be used.
     *
     * @return $this
     */
    public function segments(array $segments): self
    {
        $this->segments = $segments;
        return $this;
    }

    /**
     * Set the title of the page header.
     *
     * @param string|array $title The title to set. Can be a string or an array.
     * @return $this
     */

    public function title(string|array $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the buttons of the page header.
     *
     * @param array $buttons A list of buttons. Each button should be an array with the following keys:
     *                       - text: string
     *                       - url: string|null
     *                       - icon: string|null
     *
     * @return $this
     */
    public function buttons(array $buttons): self
    {
        $this->buttons ??= [];
        foreach ($buttons as $button) {
            $this->addButton(...$button);
        }

        return $this;
    }

    /**
     * Add a button to the page header.
     *
     * @param string $text The text of the button.
     * @param string|null $url The URL of the button. Defaults to '#'.
     * @param string|null $icon The icon of the button. Defaults to null.
     * @param string|null $type The type of the button. Defaults to 'link'. Available values: 'link', 'modal', 'back'.
     * @param string|null $target The target of the button. Defaults to null. Only applicable if $type is 'link' or 'modal'.
     * @return $this
     */
    public function addButton(
        string $text,
        ?string $url = '#',
        ?string $icon = null,
        ?string $type = 'link',
        ?string $target = null,
        ?bool $animate = false
    ): self {
        $this->buttons[] = [
            'type' => $type,
            'text' => $text,
            'icon' => $icon,
            'url' => $url,
            'target' => $target,
            'animate' => $animate,
        ];
        return $this;
    }

    /**
     * Adds a link button to the page header.
     *
     * @param string $btnText The text of the button. Defaults to 'Button'.
     * @param string $url The URL of the button.
     * @param string|null $icon The icon of the button. Defaults to null.
     * @param string|null $target The target of the button. Defaults to null.
     * @return $this
     */
    public function addLink(string $btnText = 'Button', string $url, ?string $icon = null, $type = 'link', ?string $target = null, ?bool $animate = false): self
    {
        return $this->addButton(
            $btnText,
            $url,
            $icon,
            $type,
            $target,
            $animate
        );
    }

    /**
     * Adds a button to the page header that links to the previous page.
     *
     * @param string|null $url The URL of the button. Defaults to the previous URL.
     * @param string $btnText The text of the button. Defaults to 'Back'.
     * @param string $icon The icon of the button. Defaults to 'bx:chevron-left'.
     *
     * @return $this
     */
    public function addBackLink(?string $url = null, string $btnText = 'Back', string $icon = 'bx:chevron-left'): self
    {
        return $this->addButton(
            $btnText,
            $url ?? url()->previous(),
            $icon,
            'link'
        );
    }

    /**
     * Adds a button to the page header that triggers a modal.
     *
     * @param string $btnText The text of the button.
     * @param string $target The selector of the modal that should be triggered.
     * @param string|null $icon The icon of the button. Defaults to null.
     *
     * @return $this
     */
    public function addModal(string $btnText, string $target, ?string $icon = null)
    {
        return $this->addButton(
            $btnText,
            null,
            $icon,
            'modal',
            $target
        );
    }

    /**
     * Adds multiple overviews to the page header.
     *
     * @param array $overviews Array of overview items. Each item should be an array with the following keys:
     *                         - title: string
     *                         - value: mixed
     *                         - icon: string|null
     *                         - style: string|null
     *
     * @return $this
     */
    public function overviews(array $overviews): self
    {
        $this->overviews ??= [];
        foreach ($overviews as $overview) {
            $this->addOverview(...$overview);
        }

        return $this;
    }

    /**
     * Add a new overview item to the page header.
     *
     * @param string $title
     * @param mixed $value
     * @param string|null $icon
     * @param string $style
     * @return $this
     */
    public function addOverview(string $title, $value = null, ?string $icon = null, string $style = ''): self
    {
        $this->overviews[] = [
            'title' => $title,
            'value' => $value,
            'icon' => $icon,
            'style' => $style
        ];
        return $this;
    }

    /**
     * Conditionally execute a callback on the page header object.
     *
     * If the given condition evaluates to true, the callback will be
     * executed with the page header object as its argument.
     *
     * @param bool $condition
     * @param callable $callback
     * @return $this
     */
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Returns the page header as an array.
     *
     * @return array
     */
    public static function toArray(): array
    {
        $thisIns = self::$instance ?? new static();

        return [
            'title' => $thisIns->title,
            'segments' => $thisIns->segments,
            'buttons' => $thisIns->buttons,
            'overviews' => $thisIns->overviews,
        ];
    }

    /**
     * Dump the page header and end script execution
     *
     * @return $this
     */
    public function dd(): self
    {
        dd(self::toArray());
        return $this;
    }
}
