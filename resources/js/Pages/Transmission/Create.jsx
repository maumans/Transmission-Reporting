import React, { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Box,
    Paper,
    Typography,
    TextField,
    Button,
    Grid,
    FormControl,
    InputLabel,
    Select,
    MenuItem,
    FormHelperText,
    Alert,
    CircularProgress,
    Chip,
    Stack,
    Autocomplete,
    FormControlLabel,
    Radio,
    FormLabel,
    RadioGroup,
} from '@mui/material';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { fr } from 'date-fns/locale';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { Add, List } from '@mui/icons-material';

export default function Create({ auth, rubriques, success, error }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        rubrique_id: '',
        date_arretee: new Date("2024-12-31"),
        statut: { value: 'creation', label: 'Création' },
        fichier: null,
        rubriques_selectionnees: [],
        children: [],
    });

    const [selectedFile, setSelectedFile] = useState(null);
    const [uploadSuccess, setUploadSuccess] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('transmission.store'), {
            onSuccess: () => {
                reset();
                setSelectedFile(null);
                setUploadSuccess(true);
            },
        });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setSelectedFile(file);
            setData('fichier', file);
            setUploadSuccess(false);
        }
    };

    let statutOptions = [
        { value: 'CREATION', label: 'Création' },
        { value: 'MODIFICATION', label: 'Modification' },
        { value: 'ANNULATION', label: 'Annulation' },
    ];

    useEffect(() => {
        setData(prev => ({
            ...prev,
            rubriques_selectionnees: [],
            children: rubriques.find(r => r.id === parseInt(data.rubrique_id))?.children || []
        }));
    }, [data.rubrique_id]);


    return (
        <AdminLayout
            user={auth.user}
            success={success}
            error={error}
            header={
                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Typography variant="h4" component="h1">
                        Nouvelle transmission
                    </Typography>

                    <Link href={route('transmission.index')}>
                        <Button
                            variant="contained"
                            startIcon={<List />}
                        >
                            Rapport transmission
                        </Button>
                    </Link>
                </Box>
            }
        >
            <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={fr}>
                <Box>
                    <Paper elevation={3} sx={{ p: 4 }}>

                        {uploadSuccess && (
                            <Alert severity="success" sx={{ mb: 3 }}>
                                Transmission initialisée avec succès !
                            </Alert>
                        )}

                        <form onSubmit={handleSubmit}>
                            <div className='grid gap-4'>
                                <div className='grid md:grid-cols-2 gap-4'>
                                    <DatePicker
                                        label="Date arrêtée"
                                        value={data.date_arretee}
                                        onChange={(newValue) => setData('date_arretee', newValue)}
                                        slotProps={{
                                            textField: {
                                                fullWidth: true,
                                                error: !!errors.date_arretee,
                                                helperText: errors.date_arretee,
                                            },
                                        }}
                                    />

                                    <Autocomplete
                                        options={statutOptions}
                                        value={data.statut}
                                        onChange={(e, newValue) => setData('statut', newValue)}
                                        renderInput={(params) => (
                                            <TextField
                                                {...params}
                                                label="Statut"
                                                fullWidth
                                                error={!!errors.statut}
                                                helperText={errors.statut}
                                            />
                                        )}
                                    />
                                </div>


                                <FormControl>
                                    <FormLabel id="demo-row-radio-buttons-group-label">Rubriques</FormLabel>
                                    <RadioGroup
                                        row
                                        aria-labelledby="demo-row-radio-buttons-group-label"
                                        name="row-radio-buttons-group"
                                        value={data.rubrique_id}
                                        onChange={(e) => setData('rubrique_id', e.target.value)}
                                    >
                                        {rubriques.map((rubrique) => (
                                            <FormControlLabel
                                                key={rubrique.id}
                                                value={rubrique.id}
                                                control={<Radio />}
                                                label={rubrique.nom}
                                            />
                                        ))}
                                    </RadioGroup>
                                </FormControl>

                                <Box
                                    sx={{
                                        border: '2px dashed',
                                        borderColor: 'primary.main',
                                        borderRadius: 1,
                                        p: 3,
                                        textAlign: 'center',
                                        cursor: 'pointer',
                                        '&:hover': {
                                            borderColor: 'primary.dark',
                                        },
                                    }}
                                >
                                    <input
                                        type="file"
                                        id="fichier"
                                        accept=".xlsx,.xls,.csv"
                                        onChange={handleFileChange}
                                        style={{ display: 'none' }}
                                    />
                                    <label htmlFor="fichier">
                                        <Button
                                            component="span"
                                            variant="outlined"
                                            startIcon={<CloudUploadIcon />}
                                        >
                                            {selectedFile ? (
                                                <>
                                                    <CheckCircleIcon color="success" sx={{ mr: 1 }} />
                                                    {selectedFile.name}
                                                </>
                                            ) : (
                                                'Choisir le fichier'
                                            )}
                                        </Button>
                                    </label>
                                    {errors.fichier && (
                                        <FormHelperText error sx={{ mt: 1 }}>
                                            {errors.fichier}
                                        </FormHelperText>
                                    )}
                                </Box>


                                {(data.children?.length > 0) && (
                                    <div>
                                        <div className="text-lg font-bold mb-2">
                                            Sous rubriques
                                        </div>
                                        <div className="flex gap-4 border p-2 rounded w-full">
                                            {
                                                data.children.map((child) => (
                                                    <div key={child.id} className="p-2 rounded border text-white bg-blue-400">
                                                        {child.nom}
                                                    </div>
                                                ))
                                            }
                                        </div>
                                    </div>
                                )}


                                <Button
                                    type="submit"
                                    variant="contained"
                                    color="primary"
                                    disabled={processing}
                                    startIcon={processing ? <CircularProgress size={20} /> : null}
                                    fullWidth
                                >
                                    {processing ? 'Transmission en cours...' : 'Transmettre pour validation'}
                                </Button>
                            </div>
                        </form>
                    </Paper>
                </Box>
            </LocalizationProvider>
        </AdminLayout>
    );
} 