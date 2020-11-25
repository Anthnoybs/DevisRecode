


$(document).ready(function() 
{
   let scenario = function()
   {
       let presta = $('#presta').val();

       $("#garantieRow").removeAttr("disabled");
       $("#etatRow").removeAttr("disabled");
       $('#etatRow option[value="NC."]').removeAttr("disabled");
       $('#etatRow option[value="NC."]').removeClass('bg-dark');
       $('.controlOption').prop('disabled', false);

        switch (presta) 
        {
            case 'RTE':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                $('.controlOption').prop('disabled', true);
                break;

            case 'INT':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                $('.controlOption').prop('disabled', true);
                break;

            case 'DEP':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                $('.controlOption').prop('disabled', true);
                break;

            case 'MNT':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                $('.controlOption').prop('disabled', true);
                break;

           
            
            case 'VTE':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="OCC"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('disabled', 'disabled');
                $('#etatRow option[value="NC."]').addClass('bg-dark');
               
                break;
            
            case 'ECH':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('disabled', 'disabled');
                $('#etatRow option[value="OCC"]').prop('selected', true);
                $('#etatRow option[value="NC."]').addClass('bg-dark');
                break;

            case 'PRT':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                $('.controlOption').prop('disabled', true);
                break;

            case 'RPR':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('disabled', 'disabled');
                $('#etatRow option[value="OCC"]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $('#etatRow option[value="NC."]').addClass('bg-dark');
                $('.controlOption').prop('disabled', true);
                break;

            case 'PRE':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('disabled', 'disabled');
                $('#etatRow option[value="OCC"]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $('#etatRow option[value="NC."]').addClass('bg-dark');
                $('.controlOption').prop('disabled', true);
                break;
            
            case 'LOC':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('disabled', 'disabled');
                $('#etatRow option[value="OCC"]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $('#etatRow option[value="NC."]').addClass('bg-dark');
                $('.controlOption').prop('disabled', true);
                break;
            
            case 'REP':
                $('#garantieRow option[value="00"]').prop('selected', true);
                $('#etatRow option[value="NC."]').prop('selected', true);
                $("#garantieRow").prop('disabled', 'disabled');
                $("#etatRow").prop('disabled', 'disabled');
                break;
                    
            default:
                break;
        }
   }
    
    scenario();

    $('#presta').on('change', function()
    {
        scenario();
    })
    
})
    
    
    