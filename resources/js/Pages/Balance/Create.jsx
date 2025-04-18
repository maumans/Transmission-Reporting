import React, { useState } from 'react';
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
} from '@mui/material';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { fr } from 'date-fns/locale';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { Add, List } from '@mui/icons-material';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        rubrique_id: '',
        date_arretee: new Date(),
        statut: { value: 'creation', label: 'Création' },
        fichier_balance: null,
        rubriques_selectionnees: [],
    });

    const [selectedFile, setSelectedFile] = useState(null);
    const [uploadSuccess, setUploadSuccess] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('balance.store'), {
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
            setData('fichier_balance', file);
            setUploadSuccess(false);
        }
    };

    const handleRubriqueChange = (e) => {
        setData('rubrique_id', e.target.value);
        // Réinitialiser les rubriques sélectionnées quand on change de rubrique principale
        setData('rubriques_selectionnees', []);
    };

    const handleRubriquesSelectionneesChange = (e) => {
        setData('rubriques_selectionnees', e.target.value);
    };

    let statutOptions = [
        { value: 'CREATION', label: 'Création' },
        { value: 'MODIFICATION', label: 'Modification' },
        { value: 'ANNULATION', label: 'Annulation' },
    ];

    return (
        <AdminLayout
            user={auth.user}
            header={
                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Typography variant="h4" component="h1">
                        Nouvelle balance
                    </Typography>

                    <Link href={route('balance.index')}>
                        <Button
                            variant="contained"
                            startIcon={<List />}
                        >
                            Rapport balance
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
                                Balance initialisée avec succès !
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
                                        id="fichier_balance"
                                        accept=".xlsx,.xls,.csv"
                                        onChange={handleFileChange}
                                        style={{ display: 'none' }}
                                    />
                                    <label htmlFor="fichier_balance">
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
                                                'Choisir le fichier de balance'
                                            )}
                                        </Button>
                                    </label>
                                    {errors.fichier_balance && (
                                        <FormHelperText error sx={{ mt: 1 }}>
                                            {errors.fichier_balance}
                                        </FormHelperText>
                                    )}
                                </Box>


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