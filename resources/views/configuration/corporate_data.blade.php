<script type="text/javascript" src="js/configuracion/datos_corporativos.js"></script>
<form id="corporateDataForm">
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    @foreach($corporateData as $data)
    @if($data['item']=='logo_enterprise')
    <input id="nom_foto" type="hidden" value="{{ $data['item'] ==  'logo_enterprise' ? $data['description_item'] : '' }}" />
    @endif
    @if($data['item']=='name_enterprise')
    <div class="form-group col-md-8">
        <label for="name_enterprise">Nombre de la Empresa</label>
        <input type="text" id="name_enterprise" class="form-control" value="{{ $data['item'] ==  'name_enterprise' ? $data['description_item'] : '' }}" name="name_enterprise" required>
    </div>
    @endif
    @if($data['item']=='fantasy_name_enterprise')
    <div class="form-group col-md-8">
        <label for="fantasy_name_enterprise">Nombre Fantasía</label>
        <input type="text" id="fantasy_name_enterprise" class="form-control" value="{{ $data['item'] ==  'fantasy_name_enterprise' ? $data['description_item'] : '' }}" name="fantasy_name_enterprise" required>
    </div>
    @endif
    @if($data['item']=='address_enterprise')
    <div class="form-group col-md-8">
        <label for="address_enterprise">Dirección</label>
        <input type="text" id="address_enterprise" class="form-control" name="address_enterprise" value="{{ $data['item'] ==  'address_enterprise' ? $data['description_item'] : '' }}" required>
    </div>
    @endif
    @if($data['item']=='comuna_enterprise')
    <div class="form-group col-md-8">
        <label for="comuna_enterprise">Comuna:</label>
        <select id="comuna_enterprise" name="comuna_enterprise" class="form-control selectpicker" data-dropup-auto="false" data-live-search="true" required>
            <option value="" selected disabled>Seleccione una comuna</option>
            
            @foreach($comunas as $comuna)
            <option value="{{ $comuna->id }}" {{ $comuna->nom_comuna == $data['description_item'] ? 'selected' : '' }}>
                {{ $comuna->nom_comuna }}
            @endforeach
        </select>
    </div>
    @endif
    @if($data['item']=='phone_enterprise')
    <div class="form-group col-md-8">
        <label for="phone_enterprise">Teléfono</label>
        <input type="text" id="phone_enterprise" class="form-control" value="{{ $data['item'] ==  'phone_enterprise' ? $data['description_item'] : '' }}" name="phone_enterprise" required>
    </div>
    @endif
    @if($data['item']=='logo_enterprise')
    <div class="form-group col-md-8">
       
            <label for="image" class="col-sm-2 col-form-label">Logo empresa</label>
            <div class="card" style="width: auto;">
                <img class="card-img-top" style="border:1px solid blue;margin-left: 15px;" src="{{ asset($data['item'] ==  'logo_enterprise' ? $data['description_item'] : '') }}" width="300" height="150" >
                <div class="form-group">
                    <input type="file" style="margin-left:226px;margin-top:10px" title="Solo formato jpg,png o gif" class="form-control-file" id="image">
                </div>
                <input type="button" style="margin-left:226px;margin-top:-5px" class="btn btn-success upload" value="Subir">
            </div>
        
    </div>
    @endif
    @endforeach
    <div class="form-group col-md-8">
        <button type="button" id="saveCorporateDataBtn" class="btn btn-primary">Guardar</button>
    </div>    
</form>