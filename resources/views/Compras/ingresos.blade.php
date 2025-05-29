<head>
    <link rel="stylesheet" href="css/compras/ingresos.css" />
    <script type="text/javascript" src="js/compras/ingresos.js"></script>
 </head>
<div class="col-md-12">
                       <input type="hidden" id="docu_ing" name="docu" />
                       <input type="hidden" id="docu_saldo" name="docu" />
                        <input type="hidden" id="docu_tip" />  
                        <input type="hidden" id="calenda" />
                        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                  <div class="box">
                    <div class="box-header with-border">
                          <h1 style="margin-bottom:10px" id="titulo_comp" class="box-title">Listado de compras</h1><br>
                          <button class="btn btn-primary" id="btn_fact" onclick="mostrarform(1)"><i class="fa fa-plus-circle"></i> Nueva factura de compra</button> <button class="btn btn-danger" id="btnagregar" onclick="mostrarform(2)"><i class="fa fa-plus-circle"></i> Nueva boleta de compra</button><button class="btn btn-default" id="btnagregar" onclick="mostrarform(3)"><i class="fa fa-calendar"></i> Vencimientos por mes</button>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="principal">
                          
                        <table id="tbldocumentos" data-page-length='4' class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th style="text-align:center">Tipo Doc</th>
                                <th style="text-align:center">Num Doc</th>
                                <th>Proveedor</th>
                                <th style="text-align:center">Total Doc</th>
                                <th style="text-align:center">Fecha_doc</th>
                                <th style="text-align:center">Items</th>
                                <th style="text-align:center">Ingreso</th>
                                <th>Acciones</th>
                            </thead>
                            <tbody class="listado_docs">
                              
                            </tbody>
                          </table>
                    <div class="btn-group">      
                     <button class="btn btn-info" onclick="filtro(1)"> Mostrar solo facturas sin pagar</button>
                     <button class="btn btn-warning" onclick="filtro(2)"> Mostrar facturas pagadas</button>
                     <button class="btn btn-success" onclick="filtro(3)"> Mostrar todo</button>
                    </div> 
                    </div>
                    <input type="hidden" id="form_activo" />
                    <div class="panel-body" style="height: auto; display: none;" id="formularioregistros">
                        
                          <div style="margin-top:-12px" class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>PROVEEDOR(*):</label>
                            <select id="proveedor_compra" class="selectpicker form-control" data-live-search="true" title="" required="campo obligatorio">
                            <option value="0">-- Seleccione proveedor --</option>  
                            @foreach($proveedores as $prov)
                               <option value="{{ $prov->uuid }}">{{ $prov->razon_social }}</option>
                            @endforeach
                            </select>   
                           </div>
                         
                    <div id="cabecera"> 
                         <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>TIPO DOCUMENTO(*):</label>
                            <input type="text" class="form-control" id="tip_doc" value="FACTURA DE COMPRA" disabled> 
                         </div>
                         <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>NÚMERO DOCUMENTO(*):</label>
                            <input type="number" class="form-control" id="num_doc" required>
                          </div>
                          <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>FORMA DE PAGO:</label>
                            <input type="text" class="form-control" id="fpago_compra" readonly required>
                          </div>
                          <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>PLAZO PAGO (DÍAS)(*):</label>
                            <input type="number" class="form-control" id="dias" required>
                          </div>
                          <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>FECHA DOCUMENTO(*):</label>
                            <!--<input type="date" class="form-control" id="fecha_doc" required="campo obligatorio">-->
                            <input id="fecha_doc" class="form-control" readonly required />
                          </div>
                          <div style="margin-top:-12px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>FECHA VENC. DOCUMENTO:</label>
                            <input class="form-control" id="fecha_venc_doc" disabled required>
                          </div>
                    </div>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                           
                      @php $cont = 0; @endphp

                      @foreach($impuestos as $imp)
                          @php $color = $cont == 0 ? 'color:blue;font-size:13pt;' : 'color:black;font-size:10pt;'; @endphp
                      
                          <span style="{{ $color }}">
                              {{ $imp->nom_imp }} ({{ $imp->valor_imp }}%):
                          </span>
                          <label class="val_imp" id="{{ strtoupper($imp->nom_imp) }}" style="{{ $color }}margin-right:10px;"></label>
                      
                          @if($cont == 0)
                              <br>
                          @endif
                      
                          @php $cont++; @endphp
                      @endforeach
                    </div>  
                          
                          <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <table id="detalles" class="table table-responsive table-hover table-fixed">
                              <thead style="background-color:#A9D0F5">
                                    <tr><th><button id="btnAgregarArt" type="button" data-toggle="modal" title="Agregar productos" data-target="#modalProd" class="btn btn-primary"> <span class="fa fa-plus"></span></button></th>
                                    <th>Código Producto</th>
                                    <th>Producto</th>
                                    <th style="text-align:center">Cantidad</th>
                                    <th style="text-align:center">Precio Neto</th>
                                    <th style="text-align:center">Descuento</th>
                                    <th style="text-align:center">Subtotal</th>
                                </tr></thead>
                                <tfoot style="background-color: white;">
                                    <tr><th>TOTAL NETO</th>
                                    <th id="tot_neto"></th>
                                    <th>TOTAL IMPUESTOS</th>
                                    <th id="tot_imp"></th>
                                    <th>TOTAL BRUTO</th>
                                    <th id="tot_bruto"></th>
                                    <th><input type="hidden" id="tot_neto2"><input type="hidden" id="tot_imp2"><input type="hidden" id="tot_bruto2"></th> 
                                </tr></tfoot>
                                <tbody class='compras'>
                                  
                                </tbody>
                            </table>
                          </div>

                          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-primary" id="btn_guarda_fact"><i class="fa fa-save"></i> Guardar</button>
                            <button id="btnCancelar" class="btn btn-danger" onclick="mostrarform(4)" type="button"><i class="fa fa-arrow-circle-left"></i> Volver</button>
                          </div>
              
                    </div>
                    <!--Fin centro -->
                  </div><!-- /.box -->
              </div>
              <div class="panel-body" style="height: auto; display: none;" id="formularioregistros2">
                        
                          <div style="margin-top:-12px" class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>PROVEEDOR(*):</label>
                            <select id="proveedor_compra2" class="selectpicker form-control" data-live-search="true" >
                            <option value="0">-- Seleccione proveedor --</option>  
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->uuid }}">{{ $prov->razon_social }}</option>
                            @endforeach
                            </select>   
                           </div>
                         
                    <div id="cabecera2"> 
                         <div style="margin-top:-12px" class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>TIPO DOCUMENTO(*):</label>
                            <input type="text" class="form-control" id="tip_doc2" value="BOLETA DE COMPRA" disabled> 
                         </div>
                         <div style="margin-top:-12px" class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>NÚMERO DOCUMENTO(*):</label>
                            <input type="number" class="form-control" id="num_doc2" required>
                          </div>
                          <div style="margin-top:-12px" class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <label>FECHA DOCUMENTO(*):</label>
                            <!--<input type="date" class="form-control" id="fecha_doc" required="campo obligatorio">-->
                            <input id="fecha_doc2" class="form-control" readonly required />
                          </div>
                    </div>
                         
                          <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <table id="detalles2" class="table table-responsive table-hover table-fixed">
                              <thead style="background-color:#A9D0F5">
                                    <tr><th><button id="btnAgregarArt" type="button" data-toggle="modal" title="Agregar productos" data-target="#modalProd" class="btn btn-danger"> <span class="fa fa-plus"></span></button></th>
                                    <th>Código Producto</th>
                                    <th>Producto</th>
                                    <th style="text-align:center">Cantidad</th>
                                    <th style="text-align:center">Precio</th>
                                    <th style="text-align:center">Descuento</th>
                                    <th style="text-align:center">Subtotal</th>
                                </tr></thead>
                                <tfoot style="background-color: white;">
                                    <th>TOTAL BOLETA</th>
                                    <th id="tot_boleta"></th>
                                </tr></tfoot>
                                <tbody class='compras2'>
                                  
                                </tbody>
                            </table>
                          </div>

                          <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-primary" id="btn_guarda_bol"><i class="fa fa-save"></i> Guardar</button>
                            <button id="btnCancelar" class="btn btn-danger" onclick="mostrarform(4)" type="button"><i class="fa fa-arrow-circle-left"></i> Volver</button>
                          </div>
              
                    </div>
                    <div class="panel-body table-responsive" id="formulariovenc" style="display:none">
                    <body> 
                    <div class="btn-group center" style="width:100%;text-align:center;margin-bottom:10px;margin-top:-15px">
                        <label style="width:100px;border:solid 1px;background-color:red;color:white">VENCIDA</label><label style="width:100px;border:solid 1px;background-color:orange;color:white">POR VENCER</label><label style="width:100px;border:solid 1px;background-color:green;color:white">PAGADA</label><label style="width:100px;border:solid 1px;background-color:blue;color:white">VENCE HOY</label>
                    </div>    
                    <div id='top' style="display:none">
                        <select id='locale-selector'></select>
                        
                      </div>
                      <div id='calendar' style="margin-top:-20px;"></div> 
                      <button id="btnCancelar" class="btn btn-danger" onclick="mostrarform(4)" type="button"><i class="fa fa-arrow-circle-left"></i> Volver</button>
                        </body>
                    </div>    
                    <!--Fin centro -->
                  </div><!-- /.box -->
              </div>
              <!--modal productos-->
              <div class="modal fade" id="modalProd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Seleccione un Artículo</h4>
                        </div>
                        <div class="modal-body">
                          <table id="tblarticulos" data-page-length='3' class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th>Cantidad</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Código</th>
                                <th>Stock</th>
                                <th>Imagen</th>
                            </thead>
                            <tbody class="listado_pc">
                              
                            </tbody>
                            
                          </table>
                        </div>
                        
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>        
                      </div>
                    </div>
  </div>  
  <!--fin modal-->
              <!--modal detalle compra factura-->
              <div class="modal fade" id="modalDetalleCompraFact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 id="titulo_compra" class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <div style="margin-top:-12px" id="cab_factura" class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            
                            </div>
                          <table id="tbldocumento" data-page-length='3' class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P. Neto</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                                <th>Imp 1</th>
                                <th>Imp 2</th>
                            </thead>
                            <tbody class="listado_prods">
                              
                            </tbody>
                           <tfoot style="background-color: white;">
                                <tr><th></th>
                                    <th></th>
                                    <th>NETO</th>
                                    <th id="neto_doc"></th>
                                    <th>IMPUESTOS</th>
                                    <th id="imps_doc"></th>
                                    <th>BRUTO</th>
                                    <th id="bruto_doc"></th>
                                </tr>
                            </tfoot>
                          </table>
                          <form enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-body" style="float:left">
                                    <div class="form-group">
                                        <label style="width:150px">Foto Documento</label><input type="file" accept=".gif,.jpg,.jpeg,.png" title="Solo formato jpg,png" class="form-control-file" id="image">
                                    </div>
                                    <input type="button" class="btn btn-primary upload" value="Subir">
                                </div>
                                
                            </div>
                        </form>
                        </div>
                        
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>        
                      </div>
                    </div>
  </div>  
  <!--fin modal-->
  <!--modal detalle compra boleta-->
              <div class="modal fade" id="modalDetalleCompraBol" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 id="titulo_compra2" class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <div style="margin-top:-12px" id="cab_boleta" class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            
                            </div>
                          <table id="tbldocumento" data-page-length='3' class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P. Neto</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </thead>
                            <tbody class="listado_prods2">
                              
                            </tbody>
                           <tfoot style="background-color: white;">
                                <tr><th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>TOTAL</th>
                                    <th id="bruto_doc2"></th>
                                </tr>
                            </tfoot>
                          </table>
                          <form enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-body" style="float:left">
                                    <div class="form-group">
                                        <label style="width:150px">Foto Documento</label><input type="file" accept=".gif,.jpg,.jpeg,.png" title="Solo formato jpg,png" class="form-control-file" id="image2">
                                    </div>
                                    <input type="button" class="btn btn-primary upload2" value="Subir">
                                </div>
                                
                            </div>
                        </form>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>        
                      </div>
                    </div>
  </div>  
  <!--fin modal-->
  <!--modal foto-->
  <div class="modal fade" id="ver_foto_doc" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="num_docu"></h4>
      </div>
      <div id="ver_imagen_doc" class="modal-body">
                
      </div>
      <div class="modal-footer">
        <strong id="subida_por"></strong>
      </div>   
    </div>
  </div>
</div>
 <!--modal pago factura-->
<div class="modal fade" id="modulo_pago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 id="titulo_pago" class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <div id="cab_pago" class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                <select id="fpago_pag">
                                <option value="0">--SELECCIONE FORMA DE PAGO--</option>  
                                <option value="CONTADO">CONTADO</option>
                                <option value="CHEQUE AL DIA">CHEQUE AL DIA</option>
                                <option value="CHEQUE A FECHA">CHEQUE A FECHA</option>
                                </select>   
                             </div> 
                             <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <input type="number" id="monto_pago" style="display:none" placeholder="MONTO PAGO" required>
                             </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" style="margin-left:-120px;display:none" id="num_docu_pago" placeholder="N° DOCUMENTO" required>
                            </div> 
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                                    <button type="button" style="display:none" id="btn-pagar-docu" class="btn btn-danger">Grabar pago</button>
                            </div>
                        </div>
                        
                        <div class="modal-footer text-center">
                            
                        </div>        
                      </div>
                    </div>
  </div>  
  <!--fin modal-->
  <!--modal pagos realizados-->
<div class="modal fade" id="pagos_ing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 id="titulo_pagos_ing" class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                           <table id="tblpagosing" data-page-length='3' class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th>FORMA PAGO</th>
                                <th>MONTO</th>
                                <th>DOCUMENTO</th>
                                <th>FECHA</th>
                            </thead>
                            <tbody class="listado_pagos">
                              
                            </tbody>
                          </table> 
                        </div>
                        
                        <div class="modal-footer text-center">
                            
                        </div>        
                      </div>
                    </div>
  </div>  
  <!--fin modal-->