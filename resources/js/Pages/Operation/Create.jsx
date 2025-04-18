import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
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

export default function Create({ rubriques }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        rubrique_id: '',
        date_arretee: new Date(),
        statut: 'en_attente',
        fichier_balance: null,
        rubriques_selectionnees: [],
    });

    const [selectedFile, setSelectedFile] = useState(null);
    const [uploadSuccess, setUploadSuccess] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('operation.store'), {
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

    return (
        <AdminLayout>
            <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={fr}>
                <Box sx={{ maxWidth: 800, mx: 'auto', p: 3 }}>
                    <Paper elevation={3} sx={{ p: 4 }}>
                        <Typography variant="h4" component="h1" gutterBottom>
                            Transmission
                        </Typography>

                        {uploadSuccess && (
                            <Alert severity="success" sx={{ mb: 3 }}>
                                L'opération a été créée avec succès !
                            </Alert>
                        )}

                        <form onSubmit={handleSubmit}>
                            <div className='grid gap-4'>

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