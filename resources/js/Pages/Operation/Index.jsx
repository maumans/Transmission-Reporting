import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Button,
    Typography,
    Box,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';

export default function Index({ operations, auth }) {
    return (
        <AdminLayout
            user={auth.user}
            header={
                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Typography variant="h4" component="h1">
                        Opérations
                    </Typography>

                    <Link href={route('operation.create')}>
                        <Button
                            variant="contained"
                            startIcon={<AddIcon />}
                        >
                            Nouvelle Opération
                        </Button>
                    </Link>
                </Box>
            }
        >
            <Head title="Opérations" />

            <Box className='mb-5' sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Typography variant="h4" component="h1">
                        Opérations
                    </Typography>

                    <Link href={route('operation.create')}>
                        <Button
                            variant="contained"
                            startIcon={<AddIcon />}
                        >
                            Nouvelle Opération
                        </Button>
                    </Link>
                </Box>

            <TableContainer component={Paper}>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell>Date arrêtée</TableCell>
                            <TableCell>Statut</TableCell>
                            <TableCell>Déclarant</TableCell>
                            <TableCell>Valideur</TableCell>
                            <TableCell>Actions</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {operations.data.map((operation) => (
                            <TableRow key={operation.id}>
                                <TableCell>{operation.date_arretee}</TableCell>
                                <TableCell>{operation.statut}</TableCell>
                                <TableCell>
                                    {operation.declarant.name}
                                </TableCell>
                                <TableCell>
                                    {operation.valideur?.name || '-'}
                                </TableCell>
                                <TableCell>
                                    <Link href={route('operation.show', operation.id)}>
                                        <Button
                                            size="small"
                                            startIcon={<VisibilityIcon />}
                                        >
                                            Voir
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>
        </AdminLayout>
    );
} 