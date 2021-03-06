//TODO: Need refactoring
var MapInfoComponent = (function(parent) {
    inherit(parent, MapInfoComponent);

    MapInfoComponent.UI_HTML = '<div id="@ID@">{MAP_INFO}</div>';

    function MapInfoComponent() {
        MapInfoComponent.super.constructor.apply(this);

        this._model        = new MapInfoPropertyModel();
        this._$uiContainer = null;

        /**
         * Properties of view model
         */
        this.BorderSize         = new ObservableProperty();
        this.BorderColor        = new ObservableProperty();
        this.BorderType         = new ObservableProperty();
        this.BorderRadius       = new ObservableProperty();
        this.Float              = new ObservableProperty();
        this.Clear              = new ObservableProperty();
        this.FontFamily         = new ObservableProperty();
        this.FontSize           = new ObservableProperty();
        this.FontWeight         = new ObservableProperty();
        this.FontColor          = new ObservableProperty();
        this.BackgroundColor    = new ObservableProperty();
        this.BackgroundURL      = new ObservableProperty();
        this.BackgroundRepeat   = new ObservableProperty();
        this.BackgroundPosition = new ObservableProperty();
        this.BackgroundSize     = new ObservableProperty();
        this.MarginTop          = new ObservableProperty();
        this.MarginRight        = new ObservableProperty();
        this.MarginBottom       = new ObservableProperty();
        this.MarginLeft         = new ObservableProperty();
        this.PaddingTop         = new ObservableProperty();
        this.PaddingRight       = new ObservableProperty();
        this.PaddingBottom      = new ObservableProperty();
        this.PaddingLeft        = new ObservableProperty();
        this.Align              = new ObservableProperty();
        this.Position           = new ObservableProperty();
        this.Left               = new ObservableProperty();
        this.Top                = new ObservableProperty();
        this.Right              = new ObservableProperty();
        this.Bottom             = new ObservableProperty();
    };

    /**
     * Get block component name
     *
     * @return {string} - block component name
     */
    MapInfoComponent.prototype.GetName = function() { return this._model.Name; };

    MapInfoComponent.prototype.GetRelType = function() { return 'info'; };

    /**
     * Composite component is composite object
     *
     * @return {Boolean} - true if component composite
     */
    MapInfoComponent.prototype.IsComposite = function() { return false; };

    /**
     * Get block property view
     *
     * @return {PropertyView} - block property view with assign view model component
     */
    MapInfoComponent.prototype.GetPropertyView = function() { return new MapInfoPropertyView(this); };

    /**
     * Append block component to another component (component must be composite)
     *
     * @param {Component} component - composite component
     */
    MapInfoComponent.prototype.AppendTo = function(component) {
        var instance   = this,
            $container = null,
            $ui        = null;

        if(component === null || !(component instanceof Component)) {
            throw new Error('MapInfoComponent.AppendTo: component must be instance of "Component" and not be null');
        }

        MapInfoComponent.super.AppendTo.apply(this, [component]);

        if(!component.IsComposite()) { return; }

        $container = component.GetContainer();
        if($container === null) { return; }

        $ui = $(MapInfoComponent.UI_HTML.replace('@ID@', this.GetId())).appendTo($container);
        this.SetContainer($ui);
    };

    /**
     * Get component container
     *
     * @return {*} - component container or null
     */
    MapInfoComponent.prototype.GetContainer = function() { return this._$uiContainer; };

    /**
     * Set component container
     *
     * @param {*} $container - container
     */
    MapInfoComponent.prototype.SetContainer = function($container) {
        var instance = this;

        this._$uiContainer = $container;

        this._$uiContainer.click(function(e) {
            instance.OnClick(instance, { component: instance, eventObject: e });
            ComponentsManager.GetInstance().DeselectAllComponents();
            instance.Select();

            return false;
        });
    };

    /**
     * Select block component
     */
    MapInfoComponent.prototype.Select = function() {
        if(this._$uiContainer !== null) { this._$uiContainer.addClass('component-selected'); }
    };

    /**
     * Deselect block component
     */
    MapInfoComponent.prototype.Deselect = function() {
        if(this._$uiContainer !== null) { this._$uiContainer.removeClass('component-selected'); }
    };

    /**
     * Get property by name
     */
    MapInfoComponent.prototype.GetProperty = function(name) {
        return this._model.hasOwnProperty(name) ? this._model[name]
            : null;
    };

    /**
     * Set propery for view model
     */
    MapInfoComponent.prototype.SetProperty = function(sender, args) {
        if(args === null || !('modelPropertyName' in args)) { return; }

        if(this._model.hasOwnProperty(args['modelPropertyName']) && 'newValue' in args) {
            this._model[args['modelPropertyName']] = args['newValue'];

            if(this._$uiContainer !== null && 'cssPropertyName' in args) {
                var object = JSON.parse(['{"', args['cssPropertyName'], '":"', this._model[args['modelPropertyName']], '"}'].join(''));
                this._$uiContainer.css(object);
            }

            if('properyName' in args && this.hasOwnProperty(args['propertyName'])) {
                this[args['properyName']].PropertyChanged(sender, args);
            }
        }
    };

    /**
     * Virtual function
     * Set  a data with the SerializationInfo
     *
     * @param {SerializationInfo} serializationInfo - The SerializationInfo with object data
     */
    MapInfoComponent.prototype.SetObjectData = function(serializationInfo) {
        MapInfoComponent.super.SetObjectData.apply(this, [serializationInfo]);
        this._model.SetObjectData(serializationInfo);
    };

    /**
     * Virtual function
     * Populates a SerializationInfo with the data needed to serialize the target object
     *
     * @param {SerializationInfo} serializationInfo - The SerializationInfo to populate with data
     */
    MapInfoComponent.prototype.GetObjectData = function(serializationInfo) {
        MapInfoComponent.super.GetObjectData.apply(this, [serializationInfo]);
        serializationInfo.AddValue('type', 'mapinfo');
        this._model.GetObjectData(serializationInfo);
    };

    return MapInfoComponent;
})(Component);